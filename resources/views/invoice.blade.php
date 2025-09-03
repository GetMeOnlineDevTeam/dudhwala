<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ $invoiceNo }} — Invoice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    @page { size: A4; margin: 12mm 12mm 12mm 12mm; } /* compact margins for 1-page */

    *{box-sizing:border-box}
    body{font-family:DejaVu Sans, Arial, sans-serif;margin:0;color:#111827}
    .wrap{max-width:820px;margin:0 auto;padding:0}

    /* header */
    .hdr{display:flex;align-items:center;justify-content:space-between;margin:0 0 6mm 0}
    .brand{display:flex;align-items:center;gap:8px}
    .brand img{height:40px}
    .brand h1{margin:0;font-size:16px}
    .muted{color:#6b7280;font-size:11px;line-height:1.35}
    .meta{text-align:right}
    .meta h2{margin:0 0 2px;font-size:15px}
    .meta p{margin:1px 0;font-size:11px}

    /* blocks */
    .blk{border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;margin-top:6mm;page-break-inside:auto}
    .blk.keep{page-break-inside:avoid} /* keep only small blocks together */
    .blk h3{margin:0 0 6px;font-size:13px}

    /* tables */
    table{width:100%;border-collapse:collapse}
    th,td{font-size:11px;padding:6px 8px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}
    th{background:#f9fafb;color:#374151}
    .r{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}

    /* single money table */
    .money td{border-bottom:1px dashed #e5e7eb;padding:6px 0}
    .money tr.section td{border-bottom:1px solid #d1d5db;padding-top:6px}
    .money tr.total td{font-weight:700}
    .money tr.grand td{font-size:14px;font-weight:900;border-bottom:none;padding-top:8px}

    .pill{display:inline-block;border:1px solid #e5e7eb;border-radius:9999px;padding:1px 6px;font-size:10px}
    .pill-green{background:#ecfdf5;border-color:#34d399;color:#065f46}
    .pill-amber{background:#fffbeb;border-color:#fcd34d;color:#92400e}
  </style>
</head>
<body>
<div class="wrap">

  @php
    // Safe defaults
    $rentTotal      = (float)($rentTotal ?? 0);
    $depositTotal   = (float)($depositTotal ?? 0);
    $discountTotal  = (float)($discountTotal ?? 0);
    $itemsSubtotal  = (float)($itemsSubtotal ?? 0);
    $netDue         = (float)($netDue ?? max(0, $rentTotal + $depositTotal - $discountTotal));
    $paidAtBooking  = (float)($totalPaid ?? 0);

    // MoneyBack aggregates from controller
    $takePaid       = (float)($takePaid ?? 0);        // additional collected (success)
    $paybackPaid    = (float)($paybackPaid ?? 0);     // refund given (success)
    $takePending    = (float)($takePending ?? 0);
    $paybackPending = (float)($paybackPending ?? 0);

    // Settlement outcome (user-facing wording)
    $settlementDelta = $depositTotal - $itemsSubtotal; // + => refund to user, - => extra paid
    if ($settlementDelta > 0) {
        $settlementLabel = 'Refund to you';
        $settlementAbs   = $settlementDelta;
        $settlementPill  = 'pill-green';
    } elseif ($settlementDelta < 0) {
        $settlementLabel = 'Extra you paid';
        $settlementAbs   = abs($settlementDelta);
        $settlementPill  = 'pill-amber';
    } else {
        $settlementLabel = 'Settled';
        $settlementAbs   = 0;
        $settlementPill  = 'pill-green';
    }

    // Final paid (what the user actually paid overall)
    $grandTotalPaid = round($paidAtBooking + $takePaid - $paybackPaid, 2);
  @endphp

  <!-- Header -->
  <div class="hdr">
    <div class="brand">
      <img src="{{ public_path('storage/logo/logo.png') }}" alt="Logo">
      <div>
        <h1>Gaushiya Hall</h1>
        <div class="muted">Community Hall & Event Bookings · +91 98765 43210 · hello@dudhwala.org</div>
      </div>
    </div>
    <div class="meta">
      <h2>Invoice</h2>
      <p><strong>No:</strong> {{ $invoiceNo }}</p>
      <p><strong>Date:</strong> {{ $payment->created_at->format('d M, Y') }}</p>
      <p><strong>Status:</strong> {{ ucfirst($payment->status) }}</p>
    </div>
  </div>

  <!-- Bill To + Payment (small, kept on one page section) -->
  <div class="blk keep">
    <div style="display:flex;gap:12px">
      <div style="flex:1">
        <h3>Bill To</h3>
        <div style="font-size:12px">
          <div><strong>{{ $payment->user->first_name }} {{ $payment->user->last_name }}</strong></div>
          <div class="muted">Mobile: {{ $payment->user->contact_number }}</div>
          <div class="muted">Community: {{ $communityLabel ?? '—' }}</div>
        </div>
      </div>
      <div style="flex:1">
        <h3>Payment</h3>
        <div style="font-size:12px">
          <div><strong>Method:</strong> {{ strtoupper($payment->method) }}</div>
          @if ($payment->razorpay_order_id)
            <div><strong>Razorpay Order:</strong> {{ $payment->razorpay_order_id }}</div>
          @endif
          @if ($payment->razorpay_payment_id)
            <div><strong>Razorpay Payment:</strong> {{ $payment->razorpay_payment_id }}</div>
          @endif
          @if ($payment->paid_at)
            <div><strong>Paid At:</strong> {{ \Carbon\Carbon::parse($payment->paid_at)->format('d M, Y h:i A') }}</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Booking table (small, kept together) -->
  <div class="blk keep">
    <h3>Booking</h3>
    <table>
      <thead>
      <tr>
        <th>#</th>
        <th>Venue / Slot</th>
        <th>Date</th>
        <th class="r">Rent (₹)</th>
        <th class="r">Deposit (₹)</th>
      </tr>
      </thead>
      <tbody>
      @foreach($payment->bookings as $i => $b)
        @php
          $slotName = optional($b->timeSlot)->name ?? '—';
          $start = optional($b->timeSlot)->start_time ? \Carbon\Carbon::parse($b->timeSlot->start_time)->format('g:i A') : '';
          $end   = optional($b->timeSlot)->end_time   ? \Carbon\Carbon::parse($b->timeSlot->end_time)->format('g:i A')   : '';
        @endphp
        <tr>
          <td>{{ $i+1 }}</td>
          <td>
            <strong>{{ optional($b->venue_details)->name ?? '—' }}</strong>
            <div class="muted">Slot: {{ $slotName }} {{ ($start && $end) ? "($start – $end)" : '' }}</div>
          </td>
          <td>{{ \Carbon\Carbon::parse($b->booking_date)->format('d M, Y') }}</td>
          <td class="r">{{ number_format((float)(optional($b->timeSlot)->price ?? 0), 2) }}</td>
          <td class="r">{{ number_format((float)(optional($b->timeSlot)->deposit ?? 0), 2) }}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <!-- Money summary (allowed to break if needed, but sized to fit 1 page) -->
  <div class="blk">
    <h3>Amount Summary</h3>
    <table class="money">
      <!-- Booking charges -->
      <tr><td>Hall Rent</td><td class="r">₹ {{ number_format($rentTotal, 2) }}</td></tr>
      <tr><td>Refundable Deposit</td><td class="r">₹ {{ number_format($depositTotal, 2) }}</td></tr>
      @if($discountTotal > 0)
        <tr><td>Discount (Dudhwala)</td><td class="r">− ₹ {{ number_format($discountTotal, 2) }}</td></tr>
      @endif
      <tr class="section total">
        <td>Booking Total (Rent + Deposit − Discount)</td>
        <td class="r">₹ {{ number_format($netDue, 2) }}</td>
      </tr>

      <!-- Settlement after event -->
      <tr><td>Items on Rent</td><td class="r">₹ {{ number_format($itemsSubtotal, 2) }}</td></tr>
      <tr class="total">
        <td>Settlement Result <span class="pill {{ $settlementPill }}">{{ $settlementLabel }}</span></td>
        <td class="r">{{ $settlementAbs > 0 ? '₹ '.number_format($settlementAbs,2) : '₹ 0.00' }}</td>
      </tr>

      <!-- Payments actually made -->
      <tr class="section">
  <td><strong>Paid at Booking</strong></td>
  <td class="r"><strong>₹ {{ number_format($paidAtBooking, 2) }}</strong></td>
</tr>
@if($takePaid > 0)
  <tr><td>+ Additional Settlement Paid</td><td class="r">+ ₹ {{ number_format($takePaid, 2) }}</td></tr>
@endif
@if($paybackPaid > 0)
  <tr><td>− Refunded to You</td><td class="r">− ₹ {{ number_format($paybackPaid, 2) }}</td></tr>
@endif

{{-- NEW: Net paid after refunds (this is the “decreased” value) --}}
<tr class="total">
  <td><strong>Paid (after refunds)</strong></td>
  <td class="r"><strong>₹ {{ number_format($grandTotalPaid, 2) }}</strong></td>
</tr>

<tr class="grand">
  <td>Final Amount You Paid</td>
  <td class="r"><strong>₹ {{ number_format($grandTotalPaid, 2) }}</strong></td>
</tr>

    </table>

    <p class="muted" style="margin-top:6px">
      Booking Total = Hall Rent + Deposit − Discount. After the event, items are settled against the deposit.
      If items exceed the deposit you pay the difference; if the deposit exceeds items you receive a refund.
      “Final Amount You Paid” = Paid at Booking + Additional Settlement − Refunds.
    </p>
  </div>

</div>
</body>
</html>
