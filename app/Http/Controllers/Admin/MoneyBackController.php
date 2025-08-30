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
        $types = [MoneyBack::TYPE_REFUND, MoneyBack::TAKE_MONEY];

        $query = MoneyBack::with([
            'user:id,first_name,last_name',          // keep name even if booking deleted
            'booking.user:id,first_name,last_name',
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
                   ->orWhereHas('user', function ($qu) use ($q) {
                       $qu->where('first_name', 'like', "%{$q}%")
                          ->orWhere('last_name', 'like', "%{$q}%");
                   })
                   ->orWhereHas('booking', function ($qb) use ($q) {
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
    $types = [MoneyBack::TYPE_REFUND, MoneyBack::TAKE_MONEY];

    $booking         = null;
    $paidAmount      = 0.0;  // info only
    $depositAmount   = 0.0;
    $itemsTotal      = 0.0;
    $alreadyReturned = 0.0;
    $maxAmount       = 0.0;  // cap we will show in the UI

    if ($bookingId = $request->query('booking')) {
        $booking = Bookings::with(['user','venue','timeSlot','payment'])
            ->withSum('items as items_total', 'total')
            ->find($bookingId);

        if ($booking) {
            $paidAmount      = (float) ($booking->payment?->amount ?? 0.0);
            $depositAmount   = (float) ($booking->deposit_amount ?? 0.0);

            // prefer cached items_amount if you keep it, else items_total
            $itemsTotal = $booking->items_amount !== null
                ? (float) $booking->items_amount
                : (float) (($booking->items_total) ?? 0.0);

            $alreadyReturned = (float) MoneyBack::where('booking_id', $booking->id)->where('status','success')->sum('amount');

            // ✅ CAP: only deposit is refundable, minus items and any prior payouts
            $maxAmount = max($depositAmount - $itemsTotal - $alreadyReturned, 0.0);
        }
    }

    return view('admin.MoneyBack.create', compact(
        'types', 'booking', 'paidAmount', 'depositAmount', 'itemsTotal', 'alreadyReturned', 'maxAmount'
    ));
}

    /**
     * POST /admin/money-back
     * Creates entry; if type=refund, hard delete the booking.
     */
    public function store(Request $request)
    {
        $booking = Bookings::with(['payment'])->findOrFail($request->input('booking_id'));

        // Compute the same CAP on the server (source of truth)
        $depositAmount   = (float) ($booking->deposit_amount ?? 0.0);
        // prefer cached items_amount if present, else sum live
        $itemsTotal      = (float) ($booking->items_amount ?? $booking->items()->sum('total'));
        $alreadyReturned = (float) MoneyBack::where('booking_id', $booking->id)
        ->where('status','success')
        ->sum('amount');

        $cap = max($depositAmount - $itemsTotal - $alreadyReturned, 0.0);

        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'type'       => ['required', 'in:' . MoneyBack::TYPE_REFUND . ',' . MoneyBack::TAKE_MONEY],
            'amount'     => ['required', 'numeric', 'min:0.01', 'max:' . $cap],
            'reference'  => ['nullable', 'string', 'max:190'],
            'note'       => ['nullable', 'string', 'max:2000'],
        ], [
            'amount.max' => 'Amount exceeds the remaining refundable balance.',
        ]);

        DB::transaction(function () use ($validated, $booking) {
            // 1) Create money_back row (persist user_id so name remains visible after refund deletion)
            MoneyBack::create([
                'user_id'      => $booking->user_id,
                'booking_id'   => $booking->id, // FK should be nullOnDelete so history stays
                'type'         => $validated['type'],
                'amount'       => $validated['amount'],
                'reference'    => $validated['reference'] ?? null,
                'note'         => $validated['note'] ?? null,
                'status'         => 'pending',
                'processed_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.money-back.index')
            ->with('success',
                $validated['type'] === MoneyBack::TYPE_REFUND
                    ? 'Refund recorded successfully.'
                    : 'Take back recorded successfully.'
            );
    }
    public function updateStatus(Request $request, $id)
{
    $validated = $request->validate([
        'status' => ['required', 'in:success,pending,processing'],
    ]);

    $moneyBack = MoneyBack::findOrFail($id);
    $moneyBack->status = $validated['status'];
    $moneyBack->save();

    return back()->with('success', 'Status updated successfully.');
}

}
