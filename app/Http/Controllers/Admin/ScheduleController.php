<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bookings;
use App\Models\User;
use App\Models\VenueDetail;
use App\Models\VenueTimeSlot;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ScheduleController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:schedule.view'))->only('index','events','show','slots'),
        (new Middleware('can:schedule.create'))->only('store'),
        (new Middleware('can:schedule.edit'))->only('update'),
        (new Middleware('can:schedule.delete'))->only('destroy'),
    ];
}



    public function index()
    {
        $venues = VenueDetail::orderBy('name')->get(['id', 'name']);
        $users  = User::where('role', 'user')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'contact_number']);

        return view('admin.schedule.index', compact('venues', 'users'));
    }

    /** FullCalendar data source */
    public function events(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        $rows = Bookings::with(['venue', 'timeSlot', 'user'])
            ->whereBetween('booking_date', [
                Carbon::parse($request->start)->startOfDay(),
                Carbon::parse($request->end)->endOfDay(),
            ])
            ->where('status', '!=', 'rejected')
            ->get();

        $events = $rows->map(function (Bookings $b) {
            $titleParts = [
                $b->venue->name ?? 'Venue',
                $b->timeSlot->name ?? 'Slot',
                trim(($b->user->first_name ?? '') . ' ' . ($b->user->last_name ?? '')),
            ];
            return [
                'id'    => $b->id,
                'title' => implode(' • ', array_filter($titleParts)),
                'start' => Carbon::parse($b->booking_date)->toDateString(),
                'allDay' => true,
                'backgroundColor' => $b->full_time || $b->full_venue ? '#0d6efd' : '#14b672',
                'borderColor'     => '#ffffff',
                'textColor'       => '#ffffff',
            ];
        });

        return response()->json($events);
    }

    /** Slots for venue & date, optionally excluding one booking (for edit availability) */
    public function slots(Request $request)
    {
        $request->validate([
            'venue_id' => 'required|exists:venue_details,id',
            'date'     => 'required|date',
            'exclude_booking_id' => 'nullable|integer|exists:bookings,id',
        ]);

        $venueId = (int)$request->venue_id;
        $date = Carbon::parse($request->date)->toDateString();
        $exclude = $request->exclude_booking_id ? (int)$request->exclude_booking_id : null;

        $slots = VenueTimeSlot::where('venue_id', $venueId)->orderBy('start_time')->get();

        $existing = Bookings::where('venue_id', $venueId)
            ->whereDate('booking_date', $date)
            ->where('status', '!=', 'rejected')
            ->when($exclude, fn($q) => $q->where('id', '!=', $exclude))
            ->get();

        $hasFull = $existing->contains(fn($b) => $b->full_time || $b->full_venue);

        if ($hasFull) {
            $bookedIds = $slots->pluck('id')->all();
        } else {
            $bookedIds = $existing->where('single_time', true)->pluck('time_slot_id')->all();
        }

        $payload = $slots->map(function (VenueTimeSlot $s) use ($bookedIds) {
            return [
                'id'         => $s->id,
                'name'       => $s->name,
                'start'      => (string) $s->start_time,
                'end'        => (string) $s->end_time,
                'price'      => (int) $s->price,
                'full_time'  => (bool) $s->full_time,
                'full_venue' => (bool) $s->full_venue,
                'is_booked'  => in_array($s->id, $bookedIds, true),
            ];
        });

        return response()->json($payload);
    }

    /** Create one manual booking (single slot, offline payment completed) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'date'      => 'required|date|after_or_equal:today',
            'venue_id'  => 'required|exists:venue_details,id',
            'user_id'   => 'required|exists:users,id',
            'slot_id'   => 'required|exists:venue_time_slots,id',
        ]);

        DB::beginTransaction();
        try {
            $date  = Carbon::parse($data['date'])->toDateString();
            $slot  = VenueTimeSlot::where('id', $data['slot_id'])
                ->where('venue_id', $data['venue_id'])
                ->firstOrFail();

            // conflict check
            $conflict = Bookings::where('venue_id', $data['venue_id'])
                ->whereDate('booking_date', $date)
                ->where('status', '!=', 'rejected')
                ->where(function ($q) use ($slot) {
                    $q->where('time_slot_id', $slot->id)
                        ->orWhere('full_time', true)
                        ->orWhere('full_venue', true);
                })
                ->exists();
            if ($conflict) {
                return back()->withErrors(['slot_id' => 'Selected slot is no longer available.'])->withInput();
            }

            $payment = Payment::create([
                'user_id' => $data['user_id'],
                'amount'  => (int)$slot->price,
                'method'  => 'offline',
                'status'  => 'completed',
                'paid_at' => now(),
            ]);

            Bookings::create([
                'user_id'      => $data['user_id'],
                'venue_id'     => $data['venue_id'],
                'time_slot_id' => $slot->id,
                'single_time'  => !($slot->full_time || $slot->full_venue),
                'full_venue'   => (bool) $slot->full_venue,
                'full_time'    => (bool) $slot->full_time,
                'booking_date' => $date,
                'status'       => 'completed',
                'payment_id'   => $payment->id,
                'price'        => (int)$slot->price,
            ]);

            DB::commit();
            return redirect()->route('admin.schedule')->with('success', 'Booking created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /** Return booking JSON for edit modal */
    public function show(Bookings $booking)
    {
        $booking->load(['venue', 'timeSlot', 'user', 'payment']);
        return response()->json([
            'id'        => $booking->id,
            'date'      => Carbon::parse($booking->booking_date)->toDateString(),
            'venue_id'  => $booking->venue_id,
            'user_id'   => $booking->user_id,
            'slot_id'   => $booking->time_slot_id,
            'price'     => (int)($booking->price ?? 0),
            'status'    => $booking->status,
        ]);
    }

    /** Update booking (date/venue/user/slot). Recalc payment total. */
    public function update(Request $request, Bookings $booking)
    {
        $data = $request->validate([
            'date'     => 'required|date|after_or_equal:today',
            'venue_id' => 'required|exists:venue_details,id',
            'user_id'  => 'required|exists:users,id',
            'slot_id'  => 'required|exists:venue_time_slots,id',
        ]);

        DB::beginTransaction();
        try {
            $date = Carbon::parse($data['date'])->toDateString();

            $slot = VenueTimeSlot::where('id', $data['slot_id'])
                ->where('venue_id', $data['venue_id'])
                ->firstOrFail();

            // conflict check excluding self
            $conflict = Bookings::where('venue_id', $data['venue_id'])
                ->whereDate('booking_date', $date)
                ->where('status', '!=', 'rejected')
                ->where('id', '!=', $booking->id)
                ->where(function ($q) use ($slot) {
                    $q->where('time_slot_id', $slot->id)
                        ->orWhere('full_time', true)
                        ->orWhere('full_venue', true);
                })
                ->exists();
            if ($conflict) {
                return back()->withErrors(['slot_id' => 'Selected slot is no longer available.'])->withInput();
            }

            // update booking
            $booking->fill([
                'user_id'      => $data['user_id'],
                'venue_id'     => $data['venue_id'],
                'time_slot_id' => $slot->id,
                'single_time'  => !($slot->full_time || $slot->full_venue),
                'full_venue'   => (bool) $slot->full_venue,
                'full_time'    => (bool) $slot->full_time,
                'booking_date' => $date,
                'price'        => (int)$slot->price,
            ])->save();

            // recalc payment total (if exists)
            if ($booking->payment_id) {
                $payment = Payment::find($booking->payment_id);
                if ($payment) {
                    $sum = Bookings::where('payment_id', $payment->id)->sum('price');
                    $payment->update(['amount' => (int)$sum]);
                }
            }

            DB::commit();
            return redirect()->route('admin.schedule')->with('success', 'Booking updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /** Hard delete booking (and remove payment if it becomes orphaned) */
    public function destroy(Bookings $booking)
    {
        DB::transaction(function () use ($booking) {
            $paymentId = $booking->payment_id;
            $booking->delete();

            if ($paymentId) {
                $remaining = Bookings::where('payment_id', $paymentId)->count();
                if ($remaining === 0) {
                    Payment::where('id', $paymentId)->delete();
                } else {
                    // recalc remaining payment total
                    $sum = Bookings::where('payment_id', $paymentId)->sum('price');
                    Payment::where('id', $paymentId)->update(['amount' => (int)$sum]);
                }
            }
        });

        return redirect()->route('admin.schedule')->with('success', 'Booking deleted.');
    }
}
