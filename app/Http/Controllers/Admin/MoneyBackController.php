<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookings;
use App\Models\MoneyBack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MoneyBackController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:settlement.view'))->only('index','show'),
        (new Middleware('can:settlement.create'))->only('create','store'),
        (new Middleware('can:settlement.update_status'))->only('updateStatus'),
    ];
}




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
     * GET /admin/money-back/create?booking={id}
     */
    public function create(Request $request)
    {
        $bookingId = $request->query('booking');
        abort_unless($bookingId, 404);

        $booking = Bookings::with(['user','venue','timeSlot','payment','items'])
            ->withSum('items as items_total', 'total')
            ->findOrFail($bookingId);

        // Summary numbers for UI
        $deposit    = (float) ($booking->deposit_amount ?? 0.0);
        $itemsTotal = isset($booking->items_total)
            ? (float) $booking->items_total
            : (isset($booking->items_amount) && $booking->items_amount !== null
                ? (float) $booking->items_amount
                : (float) $booking->items()->sum('total'));

        $delta         = $deposit - $itemsTotal;        // + => refund, − => collect
        $refundAmount  = $delta > 0  ? $delta : 0.0;    // Pay Back
        $collectAmount = $delta < 0  ? -$delta : 0.0;   // Take Money

        // One-per-booking: existing or default (unsaved) row
        $defaultTypeLabel = $collectAmount > 0 ? 'Take Money' : 'Pay Back';

        $moneyBack = MoneyBack::firstOrNew(
            ['booking_id' => $booking->id],
            [
                'user_id' => $booking->user_id,
                'type'    => $defaultTypeLabel, // "Pay Back" | "Take Money"
                'status'  => 'pending',
                'amount'  => null,              // keep empty on first create
            ]
        );

        return view('admin.MoneyBack.create', compact(
            'booking', 'refundAmount', 'collectAmount', 'moneyBack'
        ));
    }

    /**
     * POST /admin/money-back
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Validate payload from the form (labels as values)
        $validated = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'type'       => ['required', Rule::in(['Pay Back','Take Money'])],
            'amount'     => ['required', 'numeric', 'gt:0'],
            'status'     => ['required', Rule::in(['pending','paid'])],
            'reference'  => ['nullable', 'string', 'max:190'],
            'note'       => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = Bookings::with('items')->findOrFail($validated['booking_id']);

        // Server-side cap (source of truth)
        $deposit    = (float) ($booking->deposit_amount ?? 0.0);
        $itemsTotal = isset($booking->items_total)
            ? (float) $booking->items_total
            : (isset($booking->items_amount) && $booking->items_amount !== null
                ? (float) $booking->items_amount
                : (float) $booking->items()->sum('total'));

        $delta       = $deposit - $itemsTotal;        // + => refund, − => collect
        $maxRefund   = $delta > 0  ? $delta : 0.0;    // Pay Back
        $maxCollect  = $delta < 0  ? -$delta : 0.0;   // Take Money
        $serverMax   = $validated['type'] === 'Take Money' ? $maxCollect : $maxRefund;

        $amount = min((float) $validated['amount'], (float) $serverMax);
        if ($amount <= 0.0) {
            return back()
                ->withErrors(['amount' => 'Amount must be greater than 0 for the selected direction.'])
                ->withInput();
        }

        // Upsert one-per-booking
        $moneyBack = DB::transaction(function () use ($validated, $booking, $amount) {
            return MoneyBack::updateOrCreate(
                ['booking_id' => $booking->id],  // unique key
                [
                    'user_id'      => $booking->user_id,
                    'type'         => $validated['type'],   // "Pay Back" | "Take Money"
                    'amount'       => $validated['amount'],
                    'reference'    => $validated['reference'] ?? null,
                    'note'         => $validated['note'] ?? null,
                    'status'       => $validated['status'], // 'pending' | 'paid'
                    'processed_at' => now(),
                ]
            );
        });

        $msg = $moneyBack->wasRecentlyCreated
            ? ($validated['type'] === 'Pay Back' ? 'Refund created.' : 'Collection created.')
            : ($validated['type'] === 'Pay Back' ? 'Refund updated.' : 'Collection updated.');

        return redirect()
            ->route('admin.money-back.index')
            ->with('success', $msg);
    }
    public function updateStatus(Request $request, $id)
    {
       

        $moneyBack = MoneyBack::findOrFail($id);
        $moneyBack->status = $request->status;
        $moneyBack->save();

        return back()->with('success', 'Status updated successfully.');
    }
}
