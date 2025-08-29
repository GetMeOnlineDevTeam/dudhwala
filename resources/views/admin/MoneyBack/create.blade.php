@extends('shared.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
<style>
    .mbk-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(64, 81, 137, 0.07), 0 1.5px 4px rgba(60, 72, 100, 0.05);
        padding: 20px;
    }

    .muted {
        color: #6c757d;
    }

    .stat {
        font-weight: 600;
    }

    .cap-badge {
        background: #eef7f1;
        color: #116631;
        border: 1px solid #cfe9db;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: .8rem;
    }
</style>
@endsection

@section('title', 'Money Back - Create')

@section('content')
<div class="main-content pt-0">
    <br>

    {{-- Breadcrumb --}}
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">
            <a href="{{ route('admin.money-back.index') }}" style="color: inherit; text-decoration: none;">
                Money Back
            </a>
        </div>
        <div class="ps-2 muted">Create</div>
        <div class="ms-auto">
            <a href="{{ route('admin.money-back.index') }}" class="btn btn-outline-secondary btn-sm">
                <span class="material-icons-outlined align-middle">arrow_back</span>
                Back
            </a>
        </div>
    </div>

    {{-- Booking Summary (table) --}}
    @if ($booking)
    <div class="mbk-card mb-4">
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
                <tbody>
                    <tr>
                        <th style="width:220px">Name</th>
                        <td>{{ $booking->user->first_name }} {{ $booking->user->last_name }}</td>
                        <th style="width:220px">Venue</th>
                        <td>{{ $booking->venue->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Time Slot</th>
                        <td>{{ $booking->timeSlot->name ?? '—' }}</td>
                        <th>Booking Date</th>
                        <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Paid Amount</th>
                        <td colspan="3">
                            ₹{{ $booking->payment ? number_format($booking->payment->amount, 2) : '0.00' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Create form --}}
    <form method="POST" action="{{ route('admin.money-back.store') }}" class="mbk-card">
        @csrf

        {{-- Booking --}}
        <div class="mb-3">
            <label class="form-label">Booking</label>
            @if($booking)
            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
            <input type="text" class="form-control"
                value="#{{ $booking->id }} — {{ $booking->user->first_name }} {{ $booking->user->last_name }} — {{ $booking->venue->name ?? '—' }}"
                disabled>
            @else
            <input type="number" min="1" name="booking_id" value="{{ old('booking_id') }}"
                class="form-control @error('booking_id') is-invalid @enderror"
                placeholder="Enter Booking ID">
            @error('booking_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            @endif
        </div>

        {{-- Type & Amount (side-by-side, equal width) --}}
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label">Type</label>
                <select name="type" class="form-select @error('type') is-invalid @enderror">
                    <option value="">Select type…</option>
                    @foreach($types as $t)
                    <option value="{{ $t }}" @selected(old('type')===$t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label d-flex align-items-center gap-2">
                    Amount (₹)
                    @if(isset($maxAmount))
                    <span class="badge bg-light text-dark border">Max: ₹{{ number_format($maxAmount, 2) }}</span>
                    @endif
                </label>
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    @if(isset($maxAmount)) max="{{ number_format($maxAmount, 2, '.', '') }}" @endif
                    name="amount"
                    id="amountInput"
                    value="{{ old('amount', isset($maxAmount) ? number_format($maxAmount, 2, '.', '') : '') }}"
                    class="form-control @error('amount') is-invalid @enderror"
                    placeholder="0.00">
                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if(isset($maxAmount) && $maxAmount <= 0)
                    <div class="form-text text-danger">No refundable balance remains for this booking.
            </div>
            @endif
        </div>
</div>

{{-- Note --}}
<div class="mb-3">
    <label class="form-label">Note (optional)</label>
    <textarea name="note" rows="3"
        class="form-control @error('note') is-invalid @enderror"
        placeholder="Internal note…">{{ old('note') }}</textarea>
    @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-success" @if(isset($maxAmount) && $maxAmount<=0) disabled @endif>
        <span class="material-icons-outlined align-middle">check_circle</span>
        Save Entry
    </button>
    <a href="{{ route('admin.money-back.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
</form>
</div>
@endsection

@section('js')

<script>
    (function() {
        const amount = document.getElementById('amountInput');
        if (!amount) return;
        const max = parseFloat(amount.getAttribute('max') || '0');
        amount.addEventListener('input', () => {
            if (!max) return;
            const v = parseFloat(amount.value || '0');
            if (v > max) amount.value = max.toFixed(2);
            if (v < 0) amount.value = '0.00';
        }, {
            passive: true
        });
    })();
</script>
@endsection