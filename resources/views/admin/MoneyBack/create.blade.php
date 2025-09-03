@extends('shared.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
<style>
  .mbk-card{background:#fff;border-radius:14px;box-shadow:0 2px 8px rgba(64,81,137,.07),0 1.5px 4px rgba(60,72,100,.05);padding:20px}
  .muted{color:#6c757d}
  .stat{font-weight:600}
  .pill{display:inline-block;border-radius:999px;font-size:.8rem;padding:2px 8px;border:1px solid transparent}
  .pill-green{background:#ecfdf5;border-color:#34d399;color:#065f46}
  .pill-amber{background:#fffbeb;border-color:#fcd34d;color:#92400e}
</style>
@endsection

@section('title', 'Money Back - Create')

@section('content')
<div class="main-content pt-0">
  <br>

  {{-- Breadcrumb --}}
  <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">
      <a href="{{ route('admin.money-back.index') }}" style="color:inherit;text-decoration:none;">Money Back</a>
    </div>
    <div class="ps-2 muted">Create</div>
    <div class="ms-auto">
      <a href="{{ route('admin.money-back.index') }}" class="btn btn-outline-secondary btn-sm">
        <span class="material-icons-outlined align-middle">arrow_back</span> Back
      </a>
    </div>
  </div>

  {{-- Booking Summary --}}
  @if ($booking)
    @php
      // These are passed by controller: $refundAmount, $collectAmount
      $deposit    = (float) ($booking->deposit_amount ?? 0);
      $itemsTotal = isset($booking->items_total)
          ? (float) $booking->items_total
          : (isset($booking->items_amount) && $booking->items_amount !== null
              ? (float) $booking->items_amount
              : (float) $booking->items()->sum('total'));
      $delta = $deposit - $itemsTotal;
    @endphp

    <div class="mbk-card mb-4">
      <div class="d-flex align-items-center gap-2 mb-3">
        <div class="muted">Settlement:</div>
        @if ($collectAmount > 0)
          <span class="pill pill-amber">Collect from customer (Items &gt; Deposit)</span>
        @elseif ($refundAmount > 0)
          <span class="pill pill-green">Refund to customer (Deposit &gt; Items)</span>
        @else
          <span class="pill pill-green">No action — perfectly settled</span>
        @endif
      </div>

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
            <td>₹{{ $booking->payment ? number_format($booking->payment->amount, 2) : '0.00' }}</td>
            <th>Deposit Amount</th>
            <td>₹{{ number_format($deposit, 2) }}</td>
          </tr>
          <tr>
            <th>Items Charges</th>
            <td>₹{{ number_format($itemsTotal, 2) }}</td>
            <th>Refundable Balance</th>
            <td class="stat">
              ₹{{ number_format($refundAmount, 2) }}
              <span class="ms-2 text-muted small">(max refund)</span>
            </td>
          </tr>
          <tr>
            <th>Amount To Collect</th>
            <td class="stat">
              ₹{{ number_format($collectAmount, 2) }}
              <span class="ms-2 text-muted small">(max to collect)</span>
            </td>
            <th>Net (Deposit − Items)</th>
            <td>
              @if($delta > 0)
                +₹{{ number_format($delta, 2) }} (refund)
              @elseif($delta < 0)
                −₹{{ number_format(-$delta, 2) }} (collect)
              @else
                ₹0.00
              @endif
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    {{-- Money Back form --}}
    <div class="mbk-card">
      @if ($errors->any())
        <div class="alert alert-danger"><strong>Please fix the highlighted fields.</strong></div>
      @endif

      <form action="{{ route('admin.money-back.store') }}" method="POST" class="row g-3" novalidate>
        @csrf
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

        @php
          // Map DB type to UI labels; accept 'refund/collect' or label already stored.
          $dbType = $moneyBack->type ?? null;
          if     ($dbType === 'collect') $dbTypeLabel = 'Take Money';
          elseif ($dbType === 'refund')  $dbTypeLabel = 'Pay Back';
          elseif (in_array($dbType, ['Pay Back','Take Money'], true)) $dbTypeLabel = $dbType;
          else $dbTypeLabel = ($collectAmount > 0 ? 'Take Money' : 'Pay Back');

          $selectedTypeLabel = old('type', $dbTypeLabel);

          $existingAmount = $moneyBack->amount !== null
              ? number_format((float)$moneyBack->amount, 2, '.', '')
              : '';
          $initialAmount  = old('amount', $existingAmount);

          $dbStatus       = $moneyBack->status ?? 'pending';
          $selectedStatus = old('status', $dbStatus);

          $maxForSelection = $selectedTypeLabel === 'Take Money' ? $collectAmount : $refundAmount;
        @endphp

        <div class="col-md-4">
          <label for="directionSelect" class="form-label">Direction</label>
          <select name="type" id="directionSelect"
                  class="form-select @error('type') is-invalid @enderror" required>
            <option value="Pay Back"   {{ $selectedTypeLabel === 'Pay Back'   ? 'selected' : '' }}>Pay Back (refund)</option>
            <option value="Take Money" {{ $selectedTypeLabel === 'Take Money' ? 'selected' : '' }}>Take Money (collect)</option>
          </select>
          @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
          <div class="form-text" id="amountMaxHelp">
            Max for current selection: ₹{{ number_format($maxForSelection, 2) }}
          </div>
        </div>

        <div class="col-md-4">
          <label for="amountInput" class="form-label">Amount (₹)</label>
          <input
            type="number" step="0.01" min="0" inputmode="decimal"
            id="amountInput" name="amount"
            class="form-control @error('amount') is-invalid @enderror"
            placeholder="Amount"
            value="{{ $initialAmount }}"
            autocomplete="off"
          >
          @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
          <label for="statusSelect" class="form-label">Status</label>
          <select name="status" id="statusSelect"
                  class="form-select @error('status') is-invalid @enderror" required>
            <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="paid"    {{ $selectedStatus === 'paid'    ? 'selected' : '' }}>Paid / Completed</option>
          </select>
          @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
          <label for="referenceInput" class="form-label">Reference / Note</label>
          <input type="text" id="referenceInput" name="reference"
                 class="form-control @error('reference') is-invalid @enderror"
                 placeholder="Txn ref, UPI, cash memo etc."
                 value="{{ old('reference') }}">
          @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror

          <textarea id="noteInput" name="note" rows="2"
                    class="form-control mt-2 @error('note') is-invalid @enderror"
                    placeholder="Optional note…">{{ old('note') }}</textarea>
          @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  @endif
</div>
@endsection

@section('js')
<script>
(function(){
  // Only updates help text; does not modify amount.
  const dirSel = document.getElementById('directionSelect');
  const help   = document.getElementById('amountMaxHelp');
  if (!dirSel || !help) return;

  const refundMax  = Number('{{ number_format($refundAmount, 2, ".", "") }}');
  const collectMax = Number('{{ number_format($collectAmount, 2, ".", "") }}');

  function currentMax() {
    return dirSel.value === 'Take Money' ? collectMax : refundMax;
  }

  function syncHelp(){
    const m = currentMax();
    help.textContent = 'Max for current selection: ₹' + m.toFixed(2);
  }

  dirSel.addEventListener('change', syncHelp, {passive:true});
  syncHelp();
})();
</script>
@endsection
