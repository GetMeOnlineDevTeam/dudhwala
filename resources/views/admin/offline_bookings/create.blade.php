@extends('shared.layouts.app')

@section('title','Create Offline Booking')

@section('css')
<style>
  .card{background:#fff;border-radius:14px;box-shadow:0 2px 8px rgba(64,81,137,.07),0 1.5px 4px rgba(60,72,100,.05);padding:18px}
  .muted{color:#6b7280}
</style>
@endsection

@section('content')
<div class="main-content pt-0">
  <h4 class="mb-3">Create Offline Booking</h4>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger"><strong>Fix the errors below.</strong></div>
  @endif

  <form action="{{ route('admin.offline-bookings.store') }}" method="POST" class="row g-3" id="offlineForm">
    @csrf

    <div class="col-lg-8">
      <div class="card mb-3">
        <h6 class="mb-3">Customer</h6>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Existing User ID</label>
            <input type="number" name="user_id" class="form-control @error('user_id') is-invalid @enderror" value="{{ old('user_id') }}">
            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}">
            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <h6 class="mb-3">Booking Details</h6>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Community</label>
            <select name="community" id="communitySel" class="form-select @error('community') is-invalid @enderror">
              <option value="non-dudhwala" {{ old('community')==='non-dudhwala'?'selected':'' }}>Non Dudhwala</option>
              <option value="dudhwala" {{ old('community')==='dudhwala'?'selected':'' }}>Dudhwala</option>
            </select>
            @error('community')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Venue</label>
            <select name="venue_id" id="venueSel" class="form-select @error('venue_id') is-invalid @enderror">
              <option value="" disabled {{ old('venue_id') ? '' : 'selected' }}>Select Venue</option>
              @foreach($venues as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
              @endforeach
            </select>
            @error('venue_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" name="booking_date" id="dateInput" class="form-control @error('booking_date') is-invalid @enderror" value="{{ old('booking_date') }}">
            @error('booking_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Time Slot</label>
            <select name="slot_id" id="slotSel" class="form-select @error('slot_id') is-invalid @enderror">
              <option value="" disabled selected>Select a date & venue first</option>
            </select>
            @error('slot_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Booked slots will be disabled.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Note (optional)</label>
            <input type="text" name="note" class="form-control @error('note') is-invalid @enderror" value="{{ old('note') }}">
            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
      </div>

      <div class="card mb-3">
        <h6 class="mb-3">Payment (Offline)</h6>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Collected Amount (₹)</label>
            <input type="number" step="0.01" min="0" name="collected_amount" class="form-control @error('collected_amount') is-invalid @enderror" value="{{ old('collected_amount','0.00') }}">
            @error('collected_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Mark as Paid</label>
            <select name="mark_paid" class="form-select">
              <option value="0" {{ old('mark_paid') ? '' : 'selected' }}>No (Pending)</option>
              <option value="1" {{ old('mark_paid') ? 'selected' : '' }}>Yes</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Payment Ref (optional)</label>
            <input type="text" name="payment_reference" class="form-control" value="{{ old('payment_reference') }}">
          </div>
        </div>
      </div>

      <button class="btn btn-primary">Save Offline Booking</button>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <h6 class="mb-2">Summary</h6>
        <div class="d-flex justify-content-between"><span>Rent</span><span id="rentLbl">₹ 0</span></div>
        <div class="d-flex justify-content-between"><span>Refundable Deposit</span><span id="depLbl">₹ 0</span></div>
        <div class="d-flex justify-content-between"><span>Discount (Dudhwala)</span><span id="discLbl">− ₹ 0</span></div>
        <hr>
        <div class="d-flex justify-content-between fw-semibold"><span>Net Payable</span><span id="netLbl">₹ 0</span></div>
        <div class="small muted mt-2" id="hintPill">Select a slot to see totals</div>
      </div>
    </div>
  </form>
</div>
@if(session('invoice_url'))
  <div class="alert alert-info mt-2">
    <a href="{{ session('invoice_url') }}" class="btn btn-outline-primary btn-sm">Download Invoice</a>
  </div>
@endif

@endsection

@section('js')
<script>
(function(){
  const adminSlotsBase = @json(url('/admin/offline-bookings/slots'));

  const venues   = document.getElementById('venueSel');
  const dateInp  = document.getElementById('dateInput');
  const slotsSel = document.getElementById('slotSel');
  const commSel  = document.getElementById('communitySel');

  const rentLbl = document.getElementById('rentLbl');
  const depLbl  = document.getElementById('depLbl');
  const discLbl = document.getElementById('discLbl');
  const netLbl  = document.getElementById('netLbl');
  const hint    = document.getElementById('hintPill');

  const discountRule = @json($dudhwalaDiscount ?? 0);

  function money(n){ return '₹ ' + Number(n || 0).toLocaleString('en-IN'); }
  function computeDiscount(rent){
    const s = String(discountRule || '').trim();
    if (!s) return 0;
    const n = Number(s.replace('%','').trim());
    if (!Number.isFinite(n) || n <= 0) return 0;
    const isPercent = s.includes('%') || n <= 100;
    const d = isPercent ? Math.round((rent * n)/100) : n;
    return Math.max(0, Math.min(d, rent));
  }
  function updateSummary(opt){
    if (!opt){ rentLbl.textContent=money(0); depLbl.textContent=money(0); discLbl.textContent='− ' + money(0); netLbl.textContent=money(0); return; }
    const rent = Number(opt.getAttribute('data-price') || 0);
    const dep  = Number(opt.getAttribute('data-deposit') || 0);
    const disc = (commSel.value === 'dudhwala') ? computeDiscount(rent) : 0;
    const net  = Math.max(0, rent + dep - disc);
    rentLbl.textContent = money(rent);
    depLbl.textContent  = money(dep);
    discLbl.textContent = '− ' + money(disc);
    netLbl.textContent  = money(net);
    hint.textContent    = 'Net payable calculated from slot + discount rule';
  }

  function loadSlots(){
    const v = venues.value, d = dateInp.value;
    slotsSel.innerHTML = '<option disabled selected>Loading…</option>';
    if (!v || !d){ slotsSel.innerHTML = '<option disabled selected>Select a date & venue first</option>'; updateSummary(null); return; }

    const url = `${adminSlotsBase}/${encodeURIComponent(v)}/${encodeURIComponent(d)}`;
    fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(async (res) => {
        if (res.redirected) throw new Error('Redirected to ' + res.url);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(list => {
        slotsSel.innerHTML = '';
        if (!Array.isArray(list) || !list.length){
          slotsSel.innerHTML = '<option disabled selected>No slots found</option>';
          updateSummary(null);
          return;
        }
        const df = document.createDocumentFragment();
        list.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.slot_id;
          opt.textContent = `${s.name} — ${s.timings} (Rent ₹ ${s.price}, Dep ₹ ${s.deposit})`;
          opt.disabled = !!s.is_booked;
          opt.setAttribute('data-price', s.price || 0);
          opt.setAttribute('data-deposit', s.deposit || 0);
          df.appendChild(opt);
        });
        slotsSel.appendChild(df);
        const first = Array.from(slotsSel.options).find(o => !o.disabled);
        slotsSel.value = first ? first.value : '';
        updateSummary(first || null);
      })
      .catch(err => {
        console.error('Slots load error:', err);
        slotsSel.innerHTML = '<option disabled selected>Error loading slots</option>';
        updateSummary(null);
      });
  }

  venues.addEventListener('change', loadSlots);
  dateInp.addEventListener('change', loadSlots);
  commSel.addEventListener('change', () => {
    const opt = slotsSel.options[slotsSel.selectedIndex];
    updateSummary(opt || null);
  });
  slotsSel.addEventListener('change', () => {
    const opt = slotsSel.options[slotsSel.selectedIndex];
    updateSummary(opt || null);
  });
})();
</script>
@endsection
