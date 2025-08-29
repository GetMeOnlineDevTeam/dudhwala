<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoiceNo }} — Invoice</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <style>
        /* ===== Reset / Base ===== */
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; margin: 0; color: #111827; }
        .container { width: 100%; max-width: 820px; margin: 0 auto; padding: 28px; }

        /* ===== Header ===== */
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
        .brand { display:flex; align-items:center; gap:12px; }
        .brand img { height: 60px; }
        .brand h1 { font-size: 20px; margin: 0; }
        .muted { color:#6b7280; font-size: 12px; line-height: 1.4; }

        .meta { text-align: right; }
        .meta h2 { margin: 0 0 4px; font-size: 18px; }
        .meta p { margin: 2px 0; font-size: 12px; }

        /* ===== Sections ===== */
        .section { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 16px; margin-top: 14px; }
        .section h3 { margin: 0 0 8px; font-size: 14px; color:#111827; }

        /* ===== Grid ===== */
        .row { display: flex; gap: 14px; }
        .col { flex: 1; }

        /* ===== Table ===== */
        table { width: 100%; border-collapse: collapse; }
        th, td { font-size: 12px; padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
        th { background: #f9fafb; color: #374151; font-weight: 700; }
        tfoot td { border-top: 1px solid #e5e7eb; font-weight: 700; }

        /* ===== Totals ===== */
        .totals { margin-top: 8px; }
        .totals .line { display:flex; justify-content: space-between; font-size: 13px; margin: 4px 0; }
        .totals .grand { font-size: 16px; font-weight: 800; }

        /* ===== Notes ===== */
        .note { font-size: 11px; color:#374151; line-height:1.6; }
        .pill { display:inline-block; padding:2px 8px; border:1px solid #10b981; color:#065f46; border-radius:999px; font-size:11px; }

        /* ===== Footer ===== */
        .footer { text-align:center; margin-top: 18px; font-size: 11px; color:#6b7280; }
    </style>
</head>
<body>
<div class="container">

    {{-- Header --}}
    <div class="header">
        <div class="brand">
            {{-- Use a public_path for DomPDF --}}
            <img src="{{ public_path('storage/logo/logo.png') }}" alt="Logo">
            <div>
                <h1>{{ config('app.name', 'Gaushiya Hall') }}</h1>
                <div class="muted">
                    <div>Community Hall & Event Bookings</div>
                    <div>Phone: — • Email: —</div>
                    <div>Address: —</div>
                </div>
            </div>
        </div>
        <div class="meta">
            <h2>Invoice</h2>
            <p><strong>No:</strong> {{ $invoiceNo }}</p>
            <p><strong>Date:</strong> {{ $payment->created_at->format('d M, Y') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($payment->status) }}</p>
        </div>
    </div>

    {{-- Bill To + Payment Info --}}
    <div class="section">
        <div class="row">
            <div class="col">
                <h3>Bill To</h3>
                <div style="font-size:13px;">
                    <div><strong>{{ $payment->user->first_name }} {{ $payment->user->last_name }}</strong></div>
                    <div class="muted">Mobile: {{ $payment->user->contact_number }}</div>
                </div>
            </div>
            <div class="col">
                <h3>Payment</h3>
                <div style="font-size:13px;">
                    <div><strong>Method:</strong> {{ strtoupper($payment->method) }}</div>
                    @if($payment->razorpay_order_id)
                        <div><strong>Razorpay Order:</strong> {{ $payment->razorpay_order_id }}</div>
                    @endif
                    @if($payment->razorpay_payment_id)
                        <div><strong>Razorpay Payment:</strong> {{ $payment->razorpay_payment_id }}</div>
                    @endif
                    @if($payment->paid_at)
                        <div><strong>Paid At:</strong> {{ \Carbon\Carbon::parse($payment->paid_at)->format('d M, Y h:i A') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Booking Details --}}
    <div class="section">
        <h3>Booking Details</h3>
        <table>
            <thead>
            <tr>
                <th style="width:32px;">#</th>
                <th>Venue</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th style="text-align:right;">Rent (₹)</th>
                <th style="text-align:right;">Deposit (₹)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bookings as $idx => $b)
                @php
                    $start = optional($b->timeSlot)->start_time ? \Carbon\Carbon::parse($b->timeSlot->start_time)->format('g:i A') : '—';
                    $end   = optional($b->timeSlot)->end_time   ? \Carbon\Carbon::parse($b->timeSlot->end_time)->format('g:i A')   : '—';
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <div><strong>{{ optional($b->venue_details)->name ?? '—' }}</strong></div>
                        <div class="muted">Slot: {{ optional($b->timeSlot)->name ?? '—' }}</div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($b->booking_date)->format('d M, Y') }}</td>
                    <td>
                        {{ $start }} – {{ $end }}
                        @if(optional($b->timeSlot)->full_venue)
                            <span class="pill" style="margin-left:6px;">Full Venue</span>
                        @endif
                    </td>
                    <td style="text-align:right;">{{ number_format((float)$b->price, 2) }}</td>
                    <td style="text-align:right;">{{ number_format((float)($b->deposit ?? 0), 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <div class="line"><span>Rent Subtotal</span><span>₹ {{ number_format($rentTotal, 2) }}</span></div>
            <div class="line"><span>Refundable Deposit</span><span>₹ {{ number_format($depositTotal, 2) }}</span></div>
            <div class="line grand"><span>Total Paid</span><span>₹ {{ number_format($grandTotal, 2) }}</span></div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="section">
        <h3>Notes</h3>
        <p class="note">
            The above deposit is <strong>fully refundable</strong> after your function. If you hire items on rent
            (chairs, fans, thali, etc.), the respective charges will be <strong>deducted from this deposit</strong>.
            Any balance is refunded to the payer. Please retain this invoice for your records.
        </p>
    </div>

    <div class="footer">
        Thank you for booking with us.
    </div>
</div>
</body>
</html>
