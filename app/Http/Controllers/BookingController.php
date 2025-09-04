<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\VenueDetail;
use App\Models\VenueTimeSlot;
use App\Models\VenueFloor;
use App\Models\UserDocuments;
use App\Models\Payment;
use App\Models\MoneyBack;
use App\Models\BookingItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Razorpay\Api\Api;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BookingsExport;
use App\Models\Configuration;
use Illuminate\Support\Str;
use App\Services\InvoiceService;

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
        $request->validate([
            'community'      => 'required|in:dudhwala,non-dudhwala',
            'venue_id'       => 'required|exists:venue_details,id',
            'booking_date'   => 'required|date',
            'slot_ids'       => 'required|array|min:1',
            'slot_ids.0'     => 'exists:venue_time_slots,id',
            'payment_method' => 'required|in:online,offline',
        ]);

        $user = Auth::user();
        $slot = VenueTimeSlot::findOrFail($request->slot_ids[0]);

        // server-side totals
        $rent    = (int) $slot->price;
        $deposit = (int) ($slot->deposit ?? $slot->deposit_amount ?? 0);

        // discount rule from configurations (percent of RENT or flat ₹)
        $rule = optional(Configuration::where('key', 'dudhwala_discount')->first())->value ?? 0;

        $discount = 0;
        if ($request->community === 'dudhwala') {
            $s = (string) $rule;
            $n = (int) str_replace('%', '', $s);
            $isPercent = \Illuminate\Support\Str::contains($s, '%') || $n <= 100;
            $discount  = $isPercent ? (int) round($rent * $n / 100) : (int) $n;
            $discount  = max(0, min($discount, $rent)); // clamp to rent
        }

        $gross = $rent + $deposit;
        $net   = max(0, $gross - $discount); // what the user pays now

        DB::transaction(function () use ($request, $user, $net, $deposit, $slot, $discount, &$payment, &$booking) {
            // 1) payment (amount = NET)
            $payment = Payment::create([
                'user_id'             => $user->id,
                'amount'              => $net,
                'method'              => $request->payment_method,
                'razorpay_order_id'   => $request->input('razorpay_order_id'),
                'razorpay_payment_id' => $request->input('razorpay_payment_id'),
                'status'              => $request->payment_method === 'online' ? 'paid' : 'pending',
                'paid_at'             => $request->payment_method === 'online' ? now() : null,
            ]);

            // 2) booking (store community + discount + deposit)
            $booking = Bookings::create([
                'user_id'        => $user->id,
                'community'      => $request->community,
                'venue_id'       => $request->venue_id,
                'discount'       => $discount,
                'deposit_amount' => $deposit,
                'time_slot_id'   => $slot->id,
                'booking_date'   => \Carbon\Carbon::parse($request->booking_date)->toDateString(),
                'payment_id'     => $payment->id,
                'status'         => $payment->status === 'paid' ? 'confirmed' : 'pending',
                // keep settlement fields if you’re using them
                'settlement_status' => 'pending',
                'items_amount'      => 0,
            ]);

            // 3) MoneyBack placeholder for deposit (discount does NOT affect deposit)
            if ($deposit > 0) {
                MoneyBack::updateOrCreate(
                    ['booking_id' => $booking->id], // one row per booking
                    [
                        'user_id'  => $user->id,
                        'type'     => 'Pay Back',      // will flip later if items exceed deposit
                        'amount'   => $deposit,        // initial refundable amount
                        'status'   => 'pending',
                        'note'     => 'Auto-created at booking time for refundable deposit.',
                    ]
                );
            }
        });

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Booking created successfully.')
            ->with('invoice_url', session('invoice_url'));
    }



    // public function downloadInvoice($paymentId)
    // {
    //     // Load payment + all related bookings, each with slot/venue and items subtotal
    //     $payment = Payment::with([
    //         'user',
    //         'bookings' => function ($q) {
    //             $q->with(['timeSlot', 'venue_details'])
    //                 ->withSum('items as items_total', 'total');
    //         },
    //     ])->findOrFail($paymentId);

    //     $bookings = $payment->bookings;

    //     // Source of truth for rent/deposit is the slot rows
    //     $rentTotal = (float) $bookings->sum(fn($b) => (float) optional($b->timeSlot)->price);
    //     $depositTotal = (float) $bookings->sum(fn($b) => (float) optional($b->timeSlot)->deposit);
    //     $itemsSubtotal = (float) $bookings->sum('items_total'); // informational; not part of NET here

    //     $grossTotal = $rentTotal + $depositTotal;

    //     // Sum stored discounts; fallback to inference if missing
    //     $discountTotal = (float) $bookings->sum('discount');
    //     if ($discountTotal <= 0) {
    //         $inferred = round(max(0, $grossTotal - (float) $payment->amount), 2);
    //         if ($inferred > 0) {
    //             $discountTotal = $inferred;
    //         }
    //     }

    //     // Net charged for booking (rent+deposit-discount), and what was actually paid
    //     $netDue   = round(max(0, $grossTotal - $discountTotal), 2);
    //     $totalPaid = (float) $payment->amount;

    //     // Community label (if all bookings share the same)
    //     $communities = $bookings->pluck('community')->filter()->unique();
    //     $communityLabel = $communities->count() === 1 ? ucfirst($communities->first()) : '—';

    //     // MoneyBack (settlement) for the bookings on this invoice
    //     $bookingIds = $bookings->pluck('id')->all();
    //     $takePaid = $takePending = $paybackPaid = $paybackPending = 0.0;

    //     if (!empty($bookingIds)) {
    //         $rows = MoneyBack::select('type', 'status', DB::raw('SUM(amount) as sum'))
    //             ->whereIn('booking_id', $bookingIds)
    //             ->groupBy('type', 'status')
    //             ->get();

    //         foreach ($rows as $r) {
    //             $type = strtolower(trim($r->type ?? ''));
    //             $status = strtolower(trim($r->status ?? ''));
    //             $sum = (float) $r->sum;

    //             $isSuccess = in_array($status, ['success', 'completed', 'paid', 'approved', 'done'], true);

    //             if ($type === 'take money') {
    //                 $isSuccess ? $takePaid += $sum : $takePending += $sum;
    //             } elseif ($type === 'pay back' || $type === 'refund') {
    //                 $isSuccess ? $paybackPaid += $sum : $paybackPending += $sum;
    //             }
    //         }
    //     }

    //     // Legacy aliases so existing Blades keep working
    //     $settlementAmount   = $takePaid;       // extra collected and PAID
    //     $settlementPending  = $takePending;    // extra still due
    //     $refundAmount       = $paybackPaid;    // refund already PAID
    //     $refundPending      = $paybackPending; // refund still pending

    //     // Pretty invoice no.
    //     $invoiceNo = 'INV-' . $payment->created_at->format('Ymd') . '-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT);

    //     // Pack data for the Blade
    //     $data = compact(
    //         'payment',
    //         'bookings',
    //         'rentTotal',
    //         'depositTotal',
    //         'itemsSubtotal',
    //         'grossTotal',
    //         'discountTotal',
    //         'netDue',
    //         'totalPaid',
    //         'invoiceNo',
    //         'communityLabel',
    //         // new names
    //         'takePaid',
    //         'takePending',
    //         'paybackPaid',
    //         'paybackPending',
    //         // legacy aliases
    //         'settlementAmount',
    //         'settlementPending',
    //         'refundAmount',
    //         'refundPending'
    //     );

    //     $pdf = Pdf::loadView('invoice', $data)->setPaper('a4');
    //     return $pdf->download("invoice-{$payment->id}.pdf");
    // }
    public function downloadInvoice($paymentId, InvoiceService $builder)
{
    $payment = \App\Models\Payment::findOrFail($paymentId);
    $data = $builder->dataForPayment($payment);

    $pdf = Pdf::loadView('invoice', $data)->setPaper('a4');
    return $pdf->download("invoice-{$payment->id}.pdf");
}
}
