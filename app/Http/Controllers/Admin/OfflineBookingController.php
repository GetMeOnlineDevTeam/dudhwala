<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookings;
use App\Models\Configuration;
use App\Models\Payment;
use App\Models\User;
use App\Models\VenueDetail;
use App\Models\VenueTimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OfflineBookingController extends Controller implements HasMiddleware
{
    // OfflineBookingController
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:offline_bookings.create'))->only('create','store','slots'),
    ];
}

    public function create()
    {
        $venues = VenueDetail::orderBy('name')->get(['id','name','multi_floor']);
        $dudhwalaDiscount = optional(Configuration::where('key','dudhwala_discount')->first())->value ?? 0;

        return view('admin.offline_bookings.create', compact('venues','dudhwalaDiscount'));
    }

    /**
     * ADMIN slots JSON (no web guard; no redirect)
     */
    public function slots($venue, $date)
    {
        // 1) Safe date
        try {
            $parsedDate = Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return response()->json([], 200);
        }

        // 2) Venue + slots
        $venueModel = VenueDetail::findOrFail($venue);
        $isMulti    = (bool) $venueModel->multi_floor;

        $slots = VenueTimeSlot::where('venue_id', $venue)
            ->orderBy('floor_id')
            ->orderBy('end_time')
            ->get();

        if ($slots->isEmpty()) {
            return response()->json([]);
        }

        $slotsById = $slots->keyBy('id');

        // 3) Existing bookings that day
        $bookings = Bookings::where('venue_id', $venue)
            ->whereDate('booking_date', $parsedDate)
            ->where('status', '!=', 'rejected')
            ->get(['time_slot_id']);

        // Build intervals
        $bookedIntervalsByFloor = [];
        $venueWideIntervals     = [];

        foreach ($bookings as $b) {
            if (!$b->time_slot_id) continue;
            $bookedSlot = $slotsById->get($b->time_slot_id);
            if (!$bookedSlot) continue;

            [$bStart, $bEnd] = $this->parseSlotTimes($parsedDate, $bookedSlot->start_time, $bookedSlot->end_time);
            $isAllFloors = is_null($bookedSlot->floor_id) || ($isMulti && (bool) $bookedSlot->full_venue);

            if ($isAllFloors) {
                $venueWideIntervals[] = ['start' => $bStart, 'end' => $bEnd];
            } else {
                $floorId = (int) $bookedSlot->floor_id;
                $bookedIntervalsByFloor[$floorId][] = ['start' => $bStart, 'end' => $bEnd];
            }
        }

        $overlaps = static function (Carbon $a1, Carbon $a2, Carbon $b1, Carbon $b2): bool {
            return $a1->lt($b2) && $b1->lt($a2);
        };
        $overlapsAny = static function (Carbon $s, Carbon $e, array $intervals) use ($overlaps): bool {
            foreach ($intervals as $iv) {
                if ($overlaps($s, $e, $iv['start'], $iv['end'])) return true;
            }
            return false;
        };

        $allPerFloorIntervals = [];
        foreach ($bookedIntervalsByFloor as $list) {
            foreach ($list as $iv) $allPerFloorIntervals[] = $iv;
        }

        // 5) Response
        $results = $slots->map(function (VenueTimeSlot $slot) use (
            $parsedDate, $bookedIntervalsByFloor, $venueWideIntervals, $allPerFloorIntervals, $overlapsAny, $isMulti
        ) {
            [$s, $e] = $this->parseSlotTimes($parsedDate, $slot->start_time, $slot->end_time);

            $isAllFloorsSlot = is_null($slot->floor_id) || ($isMulti && (bool) $slot->full_venue);
            $floorId         = (int) ($slot->floor_id ?? 0);

            if ($isAllFloorsSlot) {
                $isBooked = $overlapsAny($s, $e, $venueWideIntervals) || $overlapsAny($s, $e, $allPerFloorIntervals);
            } else {
                $sameFloorIntervals = $bookedIntervalsByFloor[$floorId] ?? [];
                $isBooked = $overlapsAny($s, $e, $sameFloorIntervals) || $overlapsAny($s, $e, $venueWideIntervals);
            }

            return [
                'slot_id'    => $slot->id,
                'name'       => $slot->name,
                'timings'    => $s->format('g:i A') . ' – ' . $e->format('g:i A'),
                'price'      => (int) ($slot->price ?? 0),
                'deposit'    => (int) ($slot->deposit ?? $slot->deposit_amount ?? 0),
                'floor_id'   => $isAllFloorsSlot ? null : $floorId,
                'is_booked'  => $isBooked,
                'full_venue' => (bool) ($slot->full_venue ?? false),
                'full_time'  => (bool) ($slot->full_time  ?? false),
            ];
        })->values();

        return response()->json($results, 200);
    }

    private function parseSlotTimes(string $date, $startTime, $endTime): array
    {
        $parse = function ($t) {
            $t = trim((string) $t);
            foreach (['H:i:s', 'H:i'] as $fmt) {
                try { return Carbon::createFromFormat($fmt, $t); } catch (\Exception $e) {}
            }
            return Carbon::parse($t);
        };

        $s = Carbon::parse($date)->setTimeFrom($parse($startTime));
        $e = Carbon::parse($date)->setTimeFrom($parse($endTime));
        if ($e->lte($s)) $e->addDay();
        return [$s, $e];
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'user_id'           => ['nullable', 'exists:users,id'],
        'first_name'        => ['required_without:user_id','nullable','string','max:100'],
        'last_name'         => ['nullable','string','max:100'],
        'phone'             => ['required_without:user_id','nullable','string','max:20'],

        'community'         => ['required', Rule::in(['dudhwala','non-dudhwala'])],
        'venue_id'          => ['required','exists:venue_details,id'],
        'booking_date'      => ['required','date'],
        'slot_id'           => ['required','exists:venue_time_slots,id'],

        'collected_amount'  => ['required','numeric','gte:0'],
        'mark_paid'         => ['nullable','boolean'],

        'payment_reference' => ['nullable','string','max:190'],
        'note'              => ['nullable','string','max:1000'],
    ]);

    // Resolve/create user
    if (!empty($validated['user_id'])) {
        $user = User::findOrFail($validated['user_id']);
    } else {
        $user = User::where('contact_number', $validated['phone'])->first();
        if (!$user) {
            $user = User::create([
                'first_name'     => $validated['first_name'] ?? 'Guest',
                'last_name'      => $validated['last_name'] ?? '',
                'contact_number' => $validated['phone'] ?? '',
                'password'       => bcrypt(str()->random(16)),
            ]);
        }
    }

    // Slot + pricing
    $slot    = VenueTimeSlot::findOrFail($validated['slot_id']);
    $rent    = (int) ($slot->price ?? 0);
    $deposit = (int) ($slot->deposit ?? $slot->deposit_amount ?? 0);

    // Discount rule
    $rule     = optional(Configuration::where('key','dudhwala_discount')->first())->value ?? 0;
    $discount = 0;
    if ($validated['community'] === 'dudhwala') {
        $s = (string) $rule;
        $n = (int) str_replace('%', '', $s);
        $isPercent = str_contains($s, '%') || $n <= 100;
        $discount  = $isPercent ? (int) round($rent * $n / 100) : (int) $n;
        $discount  = max(0, min($discount, $rent));
    }

    $gross = $rent + $deposit;
    $net   = max(0, $gross - $discount);

    // No overlap for that venue/date/slot (ignore rejected)
    $date = Carbon::parse($validated['booking_date'])->toDateString();
    $overlap = Bookings::where('venue_id', $validated['venue_id'])
        ->whereDate('booking_date', $date)
        ->where('time_slot_id', $validated['slot_id'])
        ->where('status', '!=', 'rejected')
        ->exists();

    if ($overlap) {
        return back()->withErrors(['slot_id' => 'This slot is already booked.'])->withInput();
    }

    // Payment/booking statuses
    $collected      = (float) $validated['collected_amount'];
    $markPaid       = (bool) ($validated['mark_paid'] ?? false);
    $paymentStatus  = ($markPaid || $collected >= $net) ? 'paid' : 'pending';
    $bookingStatus  = $paymentStatus === 'paid' ? 'confirmed' : 'pending';

    // Create payment + booking and RETURN ids from the transaction
    $ids = DB::transaction(function () use (
        $user, $validated, $slot, $discount, $deposit, $date, $collected, $paymentStatus, $bookingStatus
    ) {
        $payment = Payment::create([
            'user_id'   => $user->id,
            'amount'    => $collected,          // offline: amount collected now
            'method'    => 'offline',
            'status'    => $paymentStatus,
            'paid_at'   => $paymentStatus === 'paid' ? now() : null,
            'reference' => $validated['payment_reference'] ?? null,
        ]);

        $booking = Bookings::create([
            'user_id'          => $user->id,
            'community'        => $validated['community'],
            'venue_id'         => $validated['venue_id'],
            'discount'         => $discount,
            'deposit_amount'   => $deposit,
            'time_slot_id'     => $slot->id,
            'booking_date'     => $date,
            'payment_id'       => $payment->id,
            'status'           => $bookingStatus,
            'settlement_status'=> 'pending',
            'items_amount'     => 0,
            'note'             => $validated['note'] ?? null,
        ]);

        return ['payment_id' => $payment->id, 'booking_id' => $booking->id];
    });

    // Flash invoice link for admin (ensure route exists: admin.payments.invoice)
    return redirect()
        ->route('admin.offline-bookings.create')
        ->with('success', 'Offline booking saved.')
        ->with('invoice_url', route('admin.payments.invoice', $ids['payment_id']));
}

}
