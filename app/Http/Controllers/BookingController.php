<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\VenueDetail;
use App\Models\VenueTimeSlot;
use App\Models\VenueFloor;
use App\Models\UserDocuments;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Razorpay\Api\Api;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;

class BookingController extends Controller
{
    /**
     * Return all slots for a venue on a given date,
     * marking those already booked.
     */
    public function getSlots($venue, $date)
    {
        // 1) Safe date parsing (avoid 500s on bad input)
        try {
            $parsedDate = Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return response()->json([]); // invalid date => empty list
        }

        // 2) Ensure venue exists, then load all slots for that venue
        $venueModel = VenueDetail::findOrFail($venue);
        $isMulti    = (bool) $venueModel->multi_floor;

        $slots = VenueTimeSlot::where('venue_id', $venue)
            ->orderBy('floor_id')      // groups per floor; null (all-floors) first
            ->orderBy('end_time')    // chronological in each group
            ->get();

        if ($slots->isEmpty()) {
            return response()->json([]);
        }

        // Quick lookup for slot meta (floor/start/end)
        $slotsById = $slots->keyBy('id');

        // 3) Fetch bookings for that venue and date (ignore rejected)
        // We only need time_slot_id; logic is floor/time/venue-wide based.
        $bookings = Bookings::where('venue_id', $venue)
            ->whereDate('booking_date', $parsedDate)
            ->where('status', '!=', 'rejected')
            ->get(['time_slot_id']);

        // 4) Build booked intervals:
        //    - per floor:   $bookedIntervalsByFloor[floor_id] = [ [start,end], ... ]
        //    - venue-wide:  $venueWideIntervals = [ [start,end], ... ]  (All-Floors slots)
        $bookedIntervalsByFloor = [];
        $venueWideIntervals     = [];

        foreach ($bookings as $b) {
            if (!$b->time_slot_id) continue;
            $bookedSlot = $slotsById->get($b->time_slot_id);
            if (!$bookedSlot) continue; // safety if slot was deleted

            [$bStart, $bEnd] = $this->parseSlotTimes(
                $parsedDate,
                $bookedSlot->start_time,
                $bookedSlot->end_time
            );

            // Treat as venue-wide if it's an All-Floors slot (floor_id null),
            // or (defensively) a full_venue slot in a multi-floor venue.
            $isAllFloors = is_null($bookedSlot->floor_id) || ($isMulti && (bool) $bookedSlot->full_venue);

            if ($isAllFloors) {
                $venueWideIntervals[] = ['start' => $bStart, 'end' => $bEnd];
            } else {
                $floorId = (int) $bookedSlot->floor_id;
                $bookedIntervalsByFloor[$floorId][] = ['start' => $bStart, 'end' => $bEnd];
            }
        }

        // Helper: interval overlap [a1,a2) with [b1,b2) iff a1 < b2 && b1 < a2
        $overlaps = static function (Carbon $a1, Carbon $a2, Carbon $b1, Carbon $b2): bool {
            return $a1->lt($b2) && $b1->lt($a2);
        };

        $overlapsAny = static function (Carbon $s, Carbon $e, array $intervals) use ($overlaps): bool {
            foreach ($intervals as $iv) {
                if ($overlaps($s, $e, $iv['start'], $iv['end'])) {
                    return true;
                }
            }
            return false;
        };

        // Flatten all per-floor intervals (used to block All-Floors choices
        // when any single floor is already booked for the overlapping time).
        $allPerFloorIntervals = [];
        foreach ($bookedIntervalsByFloor as $list) {
            foreach ($list as $iv) {
                $allPerFloorIntervals[] = $iv;
            }
        }

        // 5) Build response
        $results = $slots->map(function (VenueTimeSlot $slot) use (
            $parsedDate,
            $bookedIntervalsByFloor,
            $venueWideIntervals,
            $allPerFloorIntervals,
            $overlapsAny,
            $isMulti
        ) {
            [$s, $e] = $this->parseSlotTimes($parsedDate, $slot->start_time, $slot->end_time);

            $isAllFloorsSlot = is_null($slot->floor_id) || ($isMulti && (bool) $slot->full_venue);
            $floorId         = (int) ($slot->floor_id ?? 0);

            if ($isAllFloorsSlot) {
                // RULE A:
                // Any per-floor booking OR any existing venue-wide booking
                // overlapping this interval should block the All-Floors slot.
                $isBooked =
                    $overlapsAny($s, $e, $venueWideIntervals) ||
                    $overlapsAny($s, $e, $allPerFloorIntervals);
            } else {
                // RULE B:
                // A per-floor slot is blocked by:
                // - any booking on the SAME floor that overlaps, OR
                // - any venue-wide booking that overlaps.
                $sameFloorIntervals = $bookedIntervalsByFloor[$floorId] ?? [];
                $isBooked =
                    $overlapsAny($s, $e, $sameFloorIntervals) ||
                    $overlapsAny($s, $e, $venueWideIntervals);
            }

            return [
                'slot_id'    => $slot->id,
                'name'       => $slot->name,
                'timings'    => $s->format('g:i A') . ' – ' . $e->format('g:i A'),
                'price'      => $slot->price,
                'deposit'    => (int) $slot->deposit,
                'floor_id'   => $isAllFloorsSlot ? null : $floorId,
                'is_booked'  => $isBooked,
                // kept for UI/debug; no longer drive logic
                'full_venue' => (bool) ($slot->full_venue ?? false),
                'full_time'  => (bool) ($slot->full_time  ?? false),
            ];
        })->values();

        return response()->json($results);
    }


    /**
     * Parse two time strings into Carbon instances on the given date.
     * Accepts 'H:i:s' or 'H:i'. Handles overnight (end <= start).
     */
    private function parseSlotTimes(string $date, $startTime, $endTime): array
    {
        $parse = function ($t) {
            $t = trim((string) $t);
            foreach (['H:i:s', 'H:i'] as $fmt) {
                try {
                    return Carbon::createFromFormat($fmt, $t);
                } catch (\Exception $e) {
                    // try next format
                }
            }
            return Carbon::parse($t); // last resort
        };

        $s = Carbon::parse($date)->setTimeFrom($parse($startTime));
        $e = Carbon::parse($date)->setTimeFrom($parse($endTime));

        if ($e->lte($s)) {
            $e->addDay();
        }

        return [$s, $e];
    }



    /**
     * Create Razorpay Order (Client-side only)
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            // JS now must send "amount" in paise as an integer
            'amount' => 'required|integer|min:1',
        ]);

        try {
            $api = new Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $order = $api->order->create([
                'receipt'         => 'order_' . uniqid(),
                'amount'          => $request->amount,      // already paise
                'currency'        => 'INR',
                'payment_capture' => 1,
            ]);

            return response()->json([
                'success'  => true,
                'order_id' => $order->id,
                'amount'   => $order->amount,
                'currency' => $order->currency,
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay Order Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function completeBooking(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'venue_id'       => 'required|exists:venue_details,id',
            'slot_ids'       => 'required|array|min:1',
            'slot_ids.*'     => 'exists:venue_time_slots,id',
            'booking_date'   => 'required|date|after:today',
            'payment_method' => 'required|in:online,offline',
        ];

        if (! $user->is_verified) {
            $rules['document_type'] = 'required|string';
            $rules['document_file'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
        }

        $validated = $request->validate($rules);

        // Prevent mixing full-venue/full-time with other slots
        $slot       = VenueTimeSlot::whereIn('id', $validated['slot_ids'])->first();

        DB::beginTransaction();
        try {
            // Store document if needed
            if (! $user->is_verified && $request->hasFile('document_file')) {
                $path = $request->file('document_file')->store('user_docs', 'public');
                UserDocuments::create([
                    'user_id'       => $user->id,
                    'document_type' => $validated['document_type'],
                    'document'      => $path,
                ]);
            }

            $date  = Carbon::parse($validated['booking_date'])->toDateString();
            $total = $slot->price + $slot -> deposit;

            // Create one Booking per slot
            $bookingRecord = Bookings::create([
                'user_id'      => $user->id,
                'venue_id'     => $validated['venue_id'],
                'time_slot_id' => $slot->id,
                'single_time'  => (! $slot->full_venue && ! $slot->full_time),
                'full_venue'   => $slot->full_venue,
                'full_time'    => $slot->full_time,
                'booking_date' => $date,
                'status'       => 'pending',
                'payment_id'   => null,
                'price'        => $slot->price,
                'deposit'      => $slot->deposit
            ]);

            // Create the payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'amount'  => $total,
                'method'  => $validated['payment_method'],
                'status'  => 'pending',
                'paid_at' => $validated['payment_method'] === 'offline' ? now() : null,
            ]);

            $bookingRecord->payment_id = $payment->id;
            $bookingRecord->save();

            // If it’s an online flow and Razorpay returned IDs, mark as paid
            if (
                $validated['payment_method'] === 'online'
                && $request->filled('razorpay_payment_id')
                && $request->filled('razorpay_order_id')
            ) {
                $payment->update([
                    'user_id'            => $user->id,
                    'amount'             => $total,
                    'method'             => 'online',
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_order_id'       => $request->razorpay_order_id,
                    'offline_reference'  => null,
                    'status'         => 'completed',
                    'paid_at'        => now(),
                ]);

                Bookings::where('payment_id', $payment->id)
                    ->update(['status' => 'approved']);
            }

            DB::commit();

            $invoiceUrl = route('book.invoice', ['payment' => $payment->id]);

            return redirect()
                ->route('book.hall')
                ->with('success', 'Booking successful! 🎉')
                ->with('invoice_url', $invoiceUrl);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking Error: ' . $e->getMessage());
            return back()
                ->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

public function downloadInvoice($paymentId)
{
    $payment = Payment::with([
        'user',
        'bookings.timeSlot',        // each booking's time slot
        'bookings.venue_details',   // venue for each booking
    ])->findOrFail($paymentId);

    // All bookings attached to this payment
    $bookings = $payment->bookings;

    // Defensive defaults if older data has nulls
    $rentTotal    = (float) $bookings->sum('price');
    $depositTotal = (float) $bookings->sum('deposit'); // requires you saved deposit on each booking
    $grandTotal   = $rentTotal + $depositTotal;

    // Nice invoice number like INV-20250829-00042
    $invoiceNo = 'INV-' . now()->format('Ymd') . '-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT);

    // Optional: infer one booking date (all your rows share same date in this flow)
    $bookingDate = optional($bookings->first())->booking_date;

    $data = compact('payment', 'bookings', 'rentTotal', 'depositTotal', 'grandTotal', 'invoiceNo', 'bookingDate');

    $pdf = Pdf::loadView('invoice', $data)->setPaper('a4');

    return $pdf->download("invoice-{$payment->id}.pdf");

}
}
