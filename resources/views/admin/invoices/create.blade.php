@extends('shared.layouts.app')

@section('title','Create Invoice (Admin)')

@section('css')
<style>
.card{background:#fff;border-radius:14px;box-shadow:0 2px 8px rgba(64,81,137,.07),0 1.5px 4px rgba(60,72,100,.05);padding:18px}
.muted{color:#6b7280}
fieldset{border:1px solid #e5e7eb;border-radius:12px;padding:12px}
legend{font-weight:600;font-size:.95rem}
</style>
@endsection

@section('content')
<div class="main-content pt-0">
  <h4 class="mb-3">Create Invoice</h4>

  @if($errors->any())
    <div class="alert alert-danger">
      <strong>Please fix the errors.</strong>
      <ul class="mb-0 mt-2">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <form action="{{ route('admin.invoices.store') }}" method="POST" id="invoiceForm">
      @csrf

      <div class="mb-3">
        <label class="form-label d-block">Mode</label>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="mode" id="mPayment" value="payment" {{ old('mode','payment')==='payment'?'checked':'' }}>
          <label class="form-check-label" for="mPayment">From Payment ID</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="mode" id="mBooking" value="booking" {{ old('mode')==='booking'?'checked':'' }}>
          <label class="form-check-label" for="mBooking">From Booking ID</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="mode" id="mManual" value="manual" {{ old('mode')==='manual'?'checked':'' }}>
          <label class="form-check-label" for="mManual">Manual</label>
        </div>
      </div>

      {{-- From Payment --}}
      <fieldset class="mb-3" id="blockPayment">
        <legend>From Payment</legend>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Payment ID</label>
            <input type="number" name="payment_id" class="form-control" value="{{ old('payment_id') }}">
            <div class="form-text">Enter an existing Payment ID and submit.</div>
          </div>
        </div>
      </fieldset>

      {{-- From Booking --}}
      <fieldset class="mb-3" id="blockBooking" style="display:none;">
        <legend>From Booking</legend>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Booking ID</label>
            <input type="number" name="booking_id" class="form-control" value="{{ old('booking_id') }}">
            <div class="form-text">Enter an existing Booking ID and submit.</div>
          </div>
        </div>
      </fieldset>

      {{-- Manual --}}
      <fieldset class="mb-3" id="blockManual" style="display:none;">
        <legend>Manual Invoice</legend>

        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Invoice Date</label>
            <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date') }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Venue (optional)</label>
            <select name="venue_id" class="form-select">
              <option value="">—</option>
              @foreach($venues as $v)
                <option value="{{ $v->id }}" {{ old('venue_id')==$v->id?'selected':'' }}>{{ $v->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Booking Date (optional)</label>
            <input type="date" name="booking_date" class="form-control" value="{{ old('booking_date') }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Community</label>
            <select name="community" class="form-select">
              <option value="non-dudhwala" {{ old('community')==='non-dudhwala'?'selected':'' }}>Non Dudhwala</option>
              <option value="dudhwala" {{ old('community')==='dudhwala'?'selected':'' }}>Dudhwala</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Rent (₹)</label>
            <input type="number" step="0.01" name="rent" class="form-control" value="{{ old('rent','0.00') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Deposit (₹)</label>
            <input type="number" step="0.01" name="deposit" class="form-control" value="{{ old('deposit','0.00') }}">
          </div>
<div class="col-md-3">
  <label class="form-label">Items Amount (₹)</label>
  <input type="number" step="0.01" name="items_amount" class="form-control" value="{{ old('items_amount','0.00') }}">
  <div class="form-text">Shown on invoice for info; does not change Net Payable.</div>
</div>
          <div class="col-md-3">
            <label class="form-label">Discount Type</label>
            <select name="discount_type" class="form-select">
              <option value="flat" {{ old('discount_type')==='flat'?'selected':'' }}>Flat</option>
              <option value="percent" {{ old('discount_type')==='percent'?'selected':'' }}>Percent</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Discount Value</label>
            <input type="number" step="0.01" name="discount_value" class="form-control" value="{{ old('discount_value','0') }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Collected Amount (₹)</label>
            <input type="number" step="0.01" name="collected_amount" class="form-control" value="{{ old('collected_amount','0.00') }}">
          </div>

          <div class="col-md-8">
            <label class="form-label">Note (optional)</label>
            <input type="text" name="note" class="form-control" value="{{ old('note') }}">
          </div>
        </div>
      </fieldset>

      <button class="btn btn-primary">Generate Invoice</button>
    </form>
  </div>
</div>
@endsection

@section('js')
<script>
(function(){
  const mPayment = document.getElementById('mPayment');
  const mBooking = document.getElementById('mBooking');
  const mManual  = document.getElementById('mManual');

  const bPayment = document.getElementById('blockPayment');
  const bBooking = document.getElementById('blockBooking');
  const bManual  = document.getElementById('blockManual');

  function sync(){
    bPayment.style.display = mPayment.checked ? '' : 'none';
    bBooking.style.display = mBooking.checked ? '' : 'none';
    bManual.style.display  = mManual.checked  ? '' : 'none';
  }
  [mPayment,mBooking,mManual].forEach(r => r.addEventListener('change', sync, {passive:true}));
  sync();
})();
</script>
@endsection
