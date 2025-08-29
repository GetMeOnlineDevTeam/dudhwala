<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MoneyBack;
use App\Models\Bookings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoneyBackController extends Controller
{
    /**
     * GET /admin/money-back
     */
    public function index(Request $request)
    {
        $types = [MoneyBack::TYPE_REFUND, MoneyBack::TYPE_RETURN];

        $query = MoneyBack::with([
            'user:id,first_name,last_name',          // so we can show names even if booking is deleted
            'booking.user:id,first_name,last_name',  // when booking still exists (return case)
            'booking.venue:id,name',
            'booking.timeSlot:id,name',
        ])->latest();

        if ($type = strtolower((string) $request->get('type'))) {
            if (in_array($type, $types, true)) {
                $query->where('type', $type);
            }
        }

        if ($q = trim((string) $request->get('q'))) {
            $query->where(function ($qq) use ($q) {
                $qq->where('id', $q)
                   ->orWhere('amount', 'like', "%{$q}%")
                   ->orWhere('reference', 'like', "%{$q}%")
                   ->orWhereHas('user', function ($qu) use ($q) {            // search saved user (always present)
                       $qu->where('first_name', 'like', "%{$q}%")
                          ->orWhere('last_name', 'like', "%{$q}%");
                   })
                   ->orWhereHas('booking', function ($qb) use ($q) {         // search booking + nested
                       $qb->where('id', $q)
                          ->orWhereHas('user', function ($qu) use ($q) {
                              $qu->where('first_name', 'like', "%{$q}%")
                                 ->orWhere('last_name', 'like', "%{$q}%");
                          })
                          ->orWhereHas('venue', function ($qv) use ($q) {
                              $qv->where('name', 'like', "%{$q}%");
                          })
                          ->orWhereHas('timeSlot', function ($qt) use ($q) {
                              $qt->where('name', 'like', "%{$q}%");
                          });
                   });
            });
        }

        if ($from = $request->date('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->date('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $perPage    = (int) $request->input('per_page', 20);
        $moneyBacks = $query->paginate($perPage)->appends($request->query());

        return view('admin.MoneyBack.index', [
            'moneyBacks' => $moneyBacks,
            'types'      => $types,
        ]);
    }

    /**
     * GET /admin/money-back/create
     * Optional prefill with ?booking=ID
     */
    public function create(Request $request)
    {
        $types            = [MoneyBack::TYPE_REFUND, MoneyBack::TYPE_RETURN];
        $booking          = null;
        $paidAmount       = 0.0;
        $alreadyReturned  = 0.0;
        $maxAmount        = null;

        if ($bookingId = $request->query('booking')) {
            $booking = Bookings::with(['user', 'venue', 'timeSlot', 'payment'])->find($bookingId);

            if ($booking) {
                $paidAmount      = (float) optional($booking->payment)->amount ?? 0.0;
                $alreadyReturned = (float) MoneyBack::where('booking_id', $booking->id)->sum('amount');
                $maxAmount       = max($paidAmount - $alreadyReturned, 0.0);
            }
        }

        return view('admin.MoneyBack.create', [
            'types'            => $types,
            'booking'          => $booking,
            'paidAmount'       => $paidAmount,
            'alreadyReturned'  => $alreadyReturned,
            'maxAmount'        => $maxAmount,
        ]);
    }

    /**
     * POST /admin/money-back
     * Creates entry; if type=refund, hard delete the booking.
     */
    public function store(Request $request)
    {
        $booking = Bookings::with('payment')->findOrFail($request->input('booking_id'));

        // Cap = paid - already returned/refunded
        $paidAmount      = (float) optional($booking->payment)->amount ?? 0.0;
        $alreadyReturned = (float) MoneyBack::where('booking_id', $booking->id)->sum('amount');
        $maxAmount       = max($paidAmount - $alreadyReturned, 0.0);

        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'type'       => ['required', 'in:' . MoneyBack::TYPE_REFUND . ',' . MoneyBack::TYPE_RETURN],
            'amount'     => ['required', 'numeric', 'min:0.01', 'max:' . $maxAmount],
            'reference'  => ['nullable', 'string', 'max:190'],
            'note'       => ['nullable', 'string', 'max:2000'],
        ], [
            'amount.max' => 'Amount exceeds the remaining refundable balance.',
        ]);

        DB::transaction(function () use ($validated, $booking) {
            // 1) Always create the money_back row (persist user_id so name is visible even after refund deletion)
            $moneyBack = MoneyBack::create([
                'user_id'      => $booking->user_id,
                'booking_id'   => $booking->id,          // may be set to NULL by FK on delete (nullOnDelete)
                'type'         => $validated['type'],
                'amount'       => $validated['amount'],
                'reference'    => $validated['reference'] ?? null,
                'note'         => $validated['note'] ?? null,
                'processed_at' => now(),
            ]);

            // 2) If refund => hard delete the booking
            if ($validated['type'] === MoneyBack::TYPE_REFUND) {
                $booking->delete(); // ON DELETE SET NULL keeps the money_back row, nulling its booking_id
            }
        });

        return redirect()
            ->route('admin.money-back.index')
            ->with('success',
                $validated['type'] === MoneyBack::TYPE_REFUND
                    ? 'Refund recorded and booking deleted.'
                    : 'Return (cashback) recorded successfully.'
            );
    }
}
