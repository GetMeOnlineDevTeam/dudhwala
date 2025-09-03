<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\MoneyBack;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Build all data needed by the invoice Blade for a given payment.
     */
    public function dataForPayment(Payment $payment): array
    {
        // Load related bookings (each with slot/venue + items subtotal)
        $payment->load([
            'user',
            'bookings' => function ($q) {
                $q->with(['timeSlot', 'venue_details'])
                  ->withSum('items as items_total', 'total');
            },
        ]);

        $bookings      = $payment->bookings;
        $rentTotal     = (float) $bookings->sum(fn ($b) => (float) optional($b->timeSlot)->price);
        $depositTotal  = (float) $bookings->sum(fn ($b) => (float) optional($b->timeSlot)->deposit);
        $itemsSubtotal = (float) $bookings->sum('items_total'); // info only

        $grossTotal = $rentTotal + $depositTotal;

        // Use stored discounts; if missing, infer from payment
        $discountTotal = (float) $bookings->sum('discount');
        if ($discountTotal <= 0) {
            $inferred = round(max(0, $grossTotal - (float) $payment->amount), 2);
            if ($inferred > 0) $discountTotal = $inferred;
        }

        $netDue   = round(max(0, $grossTotal - $discountTotal), 2);
        $totalPaid = (float) $payment->amount;

        // Community label (if all bookings share the same)
        $communities    = $bookings->pluck('community')->filter()->unique();
        $communityLabel = $communities->count() === 1 ? ucfirst($communities->first()) : '—';

        // Aggregate MoneyBack for these bookings
        $bookingIds = $bookings->pluck('id')->all();
        $takePaid = $takePending = $paybackPaid = $paybackPending = 0.0;

        if (!empty($bookingIds)) {
            $rows = MoneyBack::select('type', 'status', DB::raw('SUM(amount) as sum'))
                ->whereIn('booking_id', $bookingIds)
                ->groupBy('type', 'status')
                ->get();
            $takePaid = $takePending = $paybackPaid = $paybackPending = 0.0;

            foreach ($rows as $r) {
                $type   = strtolower(trim($r->type ?? ''));
                $status = strtolower(trim($r->status ?? ''));
                $sum    = (float) $r->sum;

                $isSuccess = in_array($status, ['success','completed','paid','approved','done'], true);

                if ($type === 'take money') {
        $isSuccess ? $takePaid += $sum : $takePending += $sum;
    } elseif ($type === 'pay back' || $type === 'refund') {
        $isSuccess ? $paybackPaid += $sum : $paybackPending += $sum;
    }
            }
        }

        // Legacy aliases (if your Blade expects these)
        $settlementAmount  = $takePaid;
        $settlementPending = $takePending;
        $refundAmount      = $paybackPaid;
        $refundPending     = $paybackPending;

        // Pretty invoice number
        $invoiceNo = 'INV-' . $payment->created_at->format('Ymd') . '-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT);

        return compact(
            'payment',
            'bookings',
            'rentTotal',
            'depositTotal',
            'itemsSubtotal',
            'grossTotal',
            'discountTotal',
            'netDue',
            'totalPaid',
            'invoiceNo',
            'communityLabel',
            // new names
            'takePaid',
            'takePending',
            'paybackPaid',
            'paybackPending',
            // legacy aliases
            'settlementAmount',
            'settlementPending',
            'refundAmount',
            'refundPending'
        );
    }
}
