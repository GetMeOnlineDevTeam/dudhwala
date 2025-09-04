<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookings;
use App\Models\Payment;
use App\Models\User;
use App\Models\VenueDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class InvoiceController extends Controller implements HasMiddleware
{
    // InvoiceController
    public static function middleware(): array
    {
        return [
            new Middleware('auth:admin'),
            new Middleware('role:admin,superadmin'),
            (new Middleware('can:invoices.create'))->only('create', 'store'),
            (new Middleware('can:invoices.download'))->only('payment', 'booking'),
        ];
    }



    /**
     * Show manual invoice creator page
     */
    public function create()
    {
        $venues = VenueDetail::orderBy('name')->get(['id', 'name']);
        return view('admin.invoices.create', compact('venues'));
    }

    /**
     * Handle 3 modes:
     * - mode=payment : redirect to invoice for payment id
     * - mode=booking : redirect to invoice for booking id
     * - mode=manual  : build a one-off invoice PDF (no DB writes)
     */
    public function store(Request $request)
    {
        // Decide which flow we’re running
        $request->validate([
            'mode' => ['required', Rule::in(['payment', 'booking', 'manual'])],
        ]);

        /* ---------------------------------------------------------
     | From existing Payment ID
     * ---------------------------------------------------------*/
        if ($request->mode === 'payment') {
            $data = $request->validate([
                'payment_id' => ['required', 'integer', 'exists:payments,id'],
            ]);
            return redirect()->route('admin.payments.invoice', $data['payment_id']);
        }

        /* ---------------------------------------------------------
     | From existing Booking ID
     * ---------------------------------------------------------*/
        if ($request->mode === 'booking') {
            $data = $request->validate([
                'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            ]);
            return redirect()->route('admin.bookings.invoice', $data['booking_id']);
        }

        /* ---------------------------------------------------------
     | Manual one-off invoice (no DB writes)
     * ---------------------------------------------------------*/
        // --- Manual one-off invoice (no DB writes) ---
        $data = $request->validate([
            'first_name'       => ['required', 'string', 'max:100'],
            'last_name'        => ['nullable', 'string', 'max:100'],
            'phone'            => ['nullable', 'string', 'max:30'],

            'venue_id'         => ['nullable', 'exists:venue_details,id'],
            'booking_date'     => ['nullable', 'date'],

            'rent'             => ['required', 'numeric', 'gte:0'],
            'deposit'          => ['required', 'numeric', 'gte:0'],
            'items_amount'     => ['nullable', 'numeric', 'gte:0'],

            'discount_type'    => ['required', Rule::in(['flat', 'percent'])],
            'discount_value'   => ['required', 'numeric', 'gte:0'],

            'collected_amount' => ['required', 'numeric', 'gte:0'],

            'community'        => ['nullable', Rule::in(['dudhwala', 'non-dudhwala'])],
            'note'             => ['nullable', 'string', 'max:1000'],

            'invoice_date'     => ['nullable', 'date'],
        ]);

        $rent          = (float) $data['rent'];
        $deposit       = (float) $data['deposit'];
        $itemsSubtotal = isset($data['items_amount']) ? (float) $data['items_amount'] : 0.0;
        $discountValue = (float) $data['discount_value'];

        // discount on RENT only
        if ($data['discount_type'] === 'percent') {
            $discount = round(($rent * $discountValue) / 100, 2);
            $discount = max(0, min($discount, $rent));
        } else {
            $discount = max(0, min($discountValue, $rent));
        }

        $gross       = $rent + $deposit;
        $net         = max(0.0, $gross - $discount);
        $collected   = (float) $data['collected_amount'];
        $invoiceDate = !empty($data['invoice_date']) ? \Carbon\Carbon::parse($data['invoice_date']) : now();

        // decide payment status for header
        $paymentStatus = ($collected >= $net) ? 'paid' : 'pending';

        // optional venue label
        $venueName = null;
        if (!empty($data['venue_id'])) {
            $venue = \App\Models\VenueDetail::find($data['venue_id']);
            $venueName = $venue?->name;
        }

        // ensure booking_date is always set to something printable
        $bookingDateStr = !empty($data['booking_date'])
            ? \Carbon\Carbon::parse($data['booking_date'])->toDateString()
            : $invoiceDate->toDateString();

        // Build a "virtual" Payment that matches what the Blade expects
        $payment = (object) [
            'id'                  => 0,
            'amount'              => $collected,
            'created_at'          => $invoiceDate,
            'status'              => $paymentStatus,          // <-- for header
            'method'              => 'manual',                // <-- for payment block
            'razorpay_order_id'   => null,
            'razorpay_payment_id' => null,
            'paid_at'             => $paymentStatus === 'paid' ? $invoiceDate : null,
            'user'                => (object) [
                'first_name'     => $data['first_name'],
                'last_name'      => $data['last_name'] ?? '',
                'contact_number' => $data['phone'] ?? '',
            ],
        ];

        // One synthetic booking row to drive the table the Blade renders: $payment->bookings
        $bookings = collect([
            (object)[
                'community'      => $data['community'] ?? 'non-dudhwala',
                'discount'       => $discount,
                'booking_date'   => $bookingDateStr, // ensure string date
                'timeSlot'       => (object)[
                    'name'       => 'Manual Slot',
                    'price'      => $rent,
                    'deposit'    => $deposit,
                    'start_time' => null, // optional; Blade already checks optional()
                    'end_time'   => null,
                ],
                'venue_details'  => $venueName ? (object)['name' => $venueName] : null,
                'items_total'    => $itemsSubtotal, // informational
            ]
        ]);

        // attach to payment so Blade’s $payment->bookings works
        $payment->bookings = $bookings;

        // Totals for the view
        $rentTotal       = $rent;
        $depositTotal    = $deposit;
        $grossTotal      = $gross;
        $discountTotal   = $discount;
        $netDue          = round($net, 2);
        $totalPaid       = $collected;
        $communityLabel  = ucfirst($bookings->first()->community);

        // settlement (manual = zero)
        $takePaid = $takePending = $paybackPaid = $paybackPending = 0.0;
        $settlementAmount  = $takePaid;
        $settlementPending = $takePending;
        $refundAmount      = $paybackPaid;
        $refundPending     = $paybackPending;

        // also pass itemsSubtotal variable explicitly (your Blade uses it)
        $itemsSubtotalVar = $itemsSubtotal;

        $invoiceNo = 'INV-' . $invoiceDate->format('Ymd') . '-M' . $invoiceDate->format('His');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice', [
            'payment'          => $payment,
            'bookings'         => $bookings,
            'rentTotal'        => $rentTotal,
            'depositTotal'     => $depositTotal,
            'itemsSubtotal'    => $itemsSubtotalVar,
            'grossTotal'       => $grossTotal,
            'discountTotal'    => $discountTotal,
            'netDue'           => $netDue,
            'totalPaid'        => $totalPaid,
            'invoiceNo'        => $invoiceNo,
            'communityLabel'   => $communityLabel,
            'takePaid'         => $takePaid,
            'takePending'      => $takePending,
            'paybackPaid'      => $paybackPaid,
            'paybackPending'   => $paybackPending,
            'settlementAmount' => $settlementAmount,
            'settlementPending' => $settlementPending,
            'refundAmount'     => $refundAmount,
            'refundPending'    => $refundPending,
        ])->setPaper('a4');

        return $pdf->download("invoice-manual-{$invoiceDate->format('YmdHis')}.pdf");
    }

    /**
     * Existing flows (reuse your current invoice for Payment/Booking)
     */
    public function payment($paymentId)
    {
        $payment = Payment::with([
            'user',
            'bookings' => function ($q) {
                $q->with(['timeSlot', 'venue_details'])->withSum('items as items_total', 'total');
            },
        ])->findOrFail($paymentId);

        // Build data (inline: mirrors your earlier logic)
        $bookings      = $payment->bookings;
        $rentTotal     = (float) $bookings->sum(fn($b) => (float) optional($b->timeSlot)->price);
        $depositTotal  = (float) $bookings->sum(fn($b) => (float) optional($b->timeSlot)->deposit);
        $itemsSubtotal = (float) $bookings->sum('items_total');
        $grossTotal    = $rentTotal + $depositTotal;

        $discountTotal = (float) $bookings->sum('discount');
        if ($discountTotal <= 0) {
            $inferred = round(max(0, $grossTotal - (float) $payment->amount), 2);
            if ($inferred > 0) $discountTotal = $inferred;
        }

        $netDue        = round(max(0, $grossTotal - $discountTotal), 2);
        $totalPaid     = (float) $payment->amount;

        $communities    = $bookings->pluck('community')->filter()->unique();
        $communityLabel = $communities->count() === 1 ? ucfirst($communities->first()) : '—';

        // For brevity, settlement fields zeroed in this minimal version
        $takePaid = $takePending = $paybackPaid = $paybackPending = 0.0;
        $settlementAmount  = $takePaid;
        $settlementPending = $takePending;
        $refundAmount      = $paybackPaid;
        $refundPending     = $paybackPending;

        $invoiceNo = 'INV-' . $payment->created_at->format('Ymd') . '-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT);

        $data = compact(
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
            'takePaid',
            'takePending',
            'paybackPaid',
            'paybackPending',
            'settlementAmount',
            'settlementPending',
            'refundAmount',
            'refundPending'
        );

        $pdf = Pdf::loadView('invoice', $data)->setPaper('a4');
        return $pdf->download("invoice-{$payment->id}.pdf");
    }

    public function booking($bookingId)
    {
        $booking = Bookings::with('payment')->findOrFail($bookingId);
        $payment = $booking->payment ?? abort(404, 'No payment for this booking.');
        return $this->payment($payment->id);
    }
}
