<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookings;
use App\Models\BookingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingItemController extends Controller
{
    public function index(Bookings $booking)
    {
        $items = $booking->items()
            ->select('id','name','qty','unit_price','total')
            ->orderBy('id')
            ->get();

        $summary = [
            'count' => $items->count(),
            'subtotal' => (float) $items->sum('total'),
        ];

        return response()->json(compact('items','summary'));
    }

    public function bulkUpsert(Request $request, Bookings $booking)
    {
        $data = $request->validate([
            'items' => ['array'],
            'items.*.id'         => ['nullable','integer', Rule::exists('booking_items','id')->where('booking_id',$booking->id)],
            'items.*.name'       => ['required','string','max:120'],
            'items.*.qty'        => ['required','integer','min:1'],
            'items.*.unit_price' => ['required','numeric','min:0'],
            'deleted_ids'        => ['array'],
            'deleted_ids.*'      => [Rule::exists('booking_items','id')->where('booking_id',$booking->id)],
        ]);

        $items = $data['items'] ?? [];
        $deleted = $data['deleted_ids'] ?? [];

        DB::transaction(function () use ($booking, $items, $deleted) {
            // delete first
            if (!empty($deleted)) {
                BookingItem::where('booking_id',$booking->id)->whereIn('id',$deleted)->delete();
            }
            
            // upsert items; compute totals on server
            foreach ($items as $row) {
                $payload = [
                    'name'       => $row['name'],
                    'qty'        => (int) $row['qty'],
                    'unit_price' => (float) $row['unit_price'],
                ];
                $payload['total'] = $payload['qty'] * $payload['unit_price'];

                if (!empty($row['id'])) {
                    BookingItem::where('booking_id',$booking->id)->where('id',$row['id'])->update($payload);
                } else {
                    $payload['booking_id'] = $booking->id;
                    BookingItem::create($payload);
                }
            }

            // update items_amount on booking for quick reporting
            $itemsSubtotal = $booking->items()->sum('total');
            $booking->update(['items_amount' => $itemsSubtotal]);
        });

        $freshTotal = (float) $booking->items()->sum('total');

        return response()->json([
            'ok' => true,
            'items_subtotal' => $freshTotal
        ]);
    }

    public function destroy(Bookings $booking, BookingItem $item)
    {
        abort_unless($item->booking_id === $booking->id, 404);
        $item->delete();

        $booking->update(['items_amount' => $booking->items()->sum('total')]);

        return response()->json(['ok' => true]);
    }
}
