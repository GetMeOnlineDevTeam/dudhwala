<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookings;
use App\Models\BookingItem;
use App\Models\MoneyBack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Item;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BookingItemController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:booking_items.view'))->only('index','suggestItems'),
        (new Middleware('can:booking_items.bulk_upsert'))->only('bulkUpsert'),
        // If you keep delete for items, create a slug and gate it here.
    ];
}



    public function index(Bookings $booking)
    {
        $items = $booking->items()
            ->select('id', 'name', 'qty', 'unit_price', 'total','item_id')
            ->orderBy('id')
            ->get();

        $summary = [
            'count'    => $items->count(),
            'subtotal' => (float) $items->sum('total'),
        ];

        return response()->json(compact('items', 'summary'));
    }

    public function bulkUpsert(Request $request, Bookings $booking)
    {
        $data = $request->validate([
            'items'                 => ['array'],
            'items.*.id'            => ['nullable', 'integer', Rule::exists('booking_items', 'id')->where('booking_id', $booking->id)],
            'items.*.name'          => ['required', 'string', 'max:120'],
            'items.*.qty'           => ['required', 'integer', 'min:1'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
            'deleted_ids'           => ['array'],
            'deleted_ids.*'         => ['integer', Rule::exists('booking_items', 'id')->where('booking_id', $booking->id)],
        ]);

        $items   = $data['items']       ?? [];
        $deleted = $data['deleted_ids'] ?? [];

        DB::transaction(function () use ($booking, $items, $deleted) {
            // 1) Delete removed rows
            if (!empty($deleted)) {
                BookingItem::where('booking_id', $booking->id)
                    ->whereIn('id', $deleted)
                    ->delete();
            }

            // 2) Upsert rows (server-calculated totals)
            foreach ($items as $row) {
                // 1) resolve or create item_id
                $itemId = $row['item_id'] ?? null;
                if (!$itemId && !empty($row['name'])) {
                    $existing = Item::whereRaw('LOWER(name) = ?', [mb_strtolower($row['name'])])->first();
                    if ($existing) {
                        $itemId = $existing->id;
                    } else {
                        // create new catalog item on the fly
                        $item = Item::create(['name' => $row['name']]);
                        $itemId = $item->id;
                    }
                }

                // 2) server-calculated totals
                $payload = [
                    'item_id'    => $itemId,                 // <— link to master (may be null)
                    'name'       => $row['name'],
                    'qty'        => (int) $row['qty'],
                    'unit_price' => (float) $row['unit_price'],
                ];
                $payload['total'] = $payload['qty'] * $payload['unit_price'];

                if (!empty($row['id'])) {
                    BookingItem::where('booking_id', $booking->id)
                        ->where('id', $row['id'])
                        ->update($payload);
                } else {
                    $payload['booking_id'] = $booking->id;
                    BookingItem::create($payload);
                }
            }


            // 3) Refresh items subtotal on booking
            $itemsSubtotal = (float) $booking->items()->sum('total');
            $booking->update(['items_amount' => $itemsSubtotal]);

            // 4) Recompute MoneyBack settlement (deposit vs items)
            // $this->recomputeSettlement($booking->fresh('items'));
        });

        $freshTotal = (float) $booking->items()->sum('total');

        return response()->json([
            'ok'             => true,
            'items_subtotal' => $freshTotal,
        ]);
    }

    public function destroy(Bookings $booking, BookingItem $item)
    {
        abort_unless($item->booking_id === $booking->id, 404);

        DB::transaction(function () use ($booking, $item) {
            $item->delete();

            // update items_amount and settlement after deletion
            $itemsSubtotal = (float) $booking->items()->sum('total');
            $booking->update(['items_amount' => $itemsSubtotal]);

            $this->recomputeSettlement($booking->fresh('items'));
        });

        return response()->json(['ok' => true]);
    }

    /**
     * diff = deposit_amount - items_total
     * diff >= 0 => Pay Back  (refund diff)
     * diff < 0  => Take Money (collect |diff|)
     *
     * Discount DOES NOT affect deposit; only deposit vs items are considered here.
     */
    private function recomputeSettlement(Bookings $booking): void
    {
        $deposit    = (float) ($booking->deposit_amount ?? 0);
        $itemsTotal = (float) $booking->items()->sum('total');

        $diff  = round($deposit - $itemsTotal, 2);
        $type  = $diff >= 0 ? 'Pay Back' : 'Take Money';
        $amt   = abs($diff);

        // One MoneyBack row per booking, auto-updated
        MoneyBack::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => $booking->user_id,
                'type'    => $type,
                'amount'  => $amt,
                'status'  => 'pending',
                'note'    => 'Auto-updated from items editor',
            ]
        );

        // Optionally reflect settlement state on the booking itself
        $booking->update([
            'settlement_status' => $amt == 0.0 ? 'settled' : 'pending',
            'settled_at'        => $amt == 0.0 ? now() : null,
        ]);
    }

    public function suggestItems(Request $request)
{
    $q = trim((string) $request->get('q', ''));
    $query = Item::query()->select('items.id', 'items.name');

    if ($q !== '') {
        $term       = mb_strtolower($q);
        $like       = "%{$term}%";
        $prefix     = "{$term}%";
        $wordPrefix = "% {$term}%";

        // Rank: 0 exact, 1 prefix, 2 word-prefix, 3 substring, 4 others
        $query
            ->selectRaw("
                CASE
                  WHEN LOWER(items.name) = ?        THEN 0
                  WHEN LOWER(items.name) LIKE ?     THEN 1
                  WHEN LOWER(items.name) LIKE ?     THEN 2
                  WHEN LOWER(items.name) LIKE ?     THEN 3
                  ELSE 4
                END AS match_rank
            ", [$term, $prefix, $wordPrefix, $like])
            ->where(function ($w) use ($like, $prefix, $wordPrefix) {
                $w->whereRaw('LOWER(items.name) LIKE ?', [$like])
                  ->orWhereRaw('LOWER(items.name) LIKE ?', [$prefix])
                  ->orWhereRaw('LOWER(items.name) LIKE ?', [$wordPrefix]);
            })
            ->orderBy('match_rank')
            ->orderByRaw('CHAR_LENGTH(items.name)') // safer for multibyte
            ->orderBy('items.name');
    } else {
        $query->orderBy('items.name');
    }

    $items = $query->limit(20)->get();

    return response()->json(['items' => $items]);
}
}
