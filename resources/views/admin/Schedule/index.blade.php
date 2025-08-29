@extends('shared.layouts.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<style>
  :root{
    --brand: #116631;
    --brand-dark: #0e5428;
    --gray-900: #111827;
    --gray-800: #1f2937;
    --gray-700: #374151;
    --gray-600: #4b5563;
  }

  .calendar-card {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(64,81,137,.07), 0 1.5px 4px rgba(60,72,100,.05);
  }
  .calendar-card .card-header { background: linear-gradient(90deg,#f8fafc,#f3f4f6); }

  /* Calendar palette */
  .fc .fc-toolbar-title{ font-size:1.05rem; font-weight:600; color: var(--gray-800); }
  .fc .fc-col-header-cell-cushion{ color: var(--gray-700); }
  .fc .fc-daygrid-day-number{ color: var(--gray-900); }
  .fc a { color: inherit; text-decoration: none; }
  .fc .fc-daygrid-event{ border-radius:8px; padding:2px 6px; }

  /* FullCalendar buttons -> brand */
  .fc .fc-button{ border-radius: 10px; }
  .fc .fc-button, .fc .fc-button-primary{
    background-color: var(--brand);
    border-color: var(--brand);
    color: #fff;
  }
  .fc .fc-button:hover,
  .fc .fc-button:focus,
  .fc .fc-button-primary:not(:disabled).fc-button-active{
    background-color: var(--brand-dark);
    border-color: var(--brand-dark);
    color: #fff;
  }
  .fc .fc-button:disabled{
    opacity: .65;
    background-color: var(--brand);
    border-color: var(--brand);
    color: #fff;
  }

  /* Past date styling (visible but non-selectable) */
  .fc-day-past-disabled { opacity:.45; }
  .fc-day-past-disabled .fc-daygrid-day-frame { cursor:not-allowed; }

  /* Brand buttons (Bootstrap overrides used on page) */
  .btn-success{
    background-color: var(--brand) !important;
    border-color: var(--brand) !important;
    color: #fff !important;
  }
  .btn-success:hover,
  .btn-success:focus{
    background-color: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
    color: #fff !important;
  }
  .btn-outline-secondary{
    background-color: #fff !important;
    border-color: var(--brand) !important;
    color: var(--brand) !important;
  }
  .btn-outline-secondary:hover,
  .btn-outline-secondary:focus{
    background-color: var(--brand) !important;
    border-color: var(--brand) !important;
    color: #fff !important;
  }

  /* Slot UI */
  .slot-grid{ display:flex; flex-wrap:wrap; gap:10px; }
  .slot-card{
    display:flex; align-items:center; gap:10px;
    border:1px solid #e5e7eb; border-radius:12px; padding:10px 12px; background:#fff;
    transition: box-shadow .15s ease, border-color .15s ease;
  }
  .slot-card:hover{ box-shadow: 0 2px 10px rgba(0,0,0,.06); border-color:#d1d5db; }
  .slot-card.booked{ opacity:.45; filter:grayscale(.3); pointer-events:none; }
  .slot-title{ font-weight:600; color: var(--gray-900); }
  .slot-meta{ color: var(--gray-600); font-size:.85rem; }
  .slot-badge{ background:#eef2ff; color:#1d4ed8; padding:2px 6px; border-radius:6px; font-size:.7rem; margin-left:6px; }
  .slot-price{ font-weight:700; color: var(--gray-900); }
  .total-box{ font-weight:700; color: var(--gray-900); }
  .muted-hint{ color:#6b7280; font-size:.9rem; }
</style>
@endsection

@section('title','Schedule')

@section('content')
<div class="main-content">
  <div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">Schedule</h4>
      @if(session('success'))
        <div class="alert alert-success mb-0 py-1 px-2">{{ session('success') }}</div>
      @endif
    </div>

    <div class="card calendar-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Calendar</span>
        <span class="text-muted small">Click a future date to add a booking • Click any booking to edit</span>
      </div>
      <div class="card-body p-3">
        <div id="calendar"></div>
      </div>
    </div>
  </div>
</div>

{{-- Create Booking Modal --}}
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" method="POST" action="{{ route('admin.schedule.store') }}">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Add Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" id="bk-date" name="date" required>
          </div>

          <div class="col-md-8">
            <label class="form-label">Venue</label>
            <select class="form-select" id="bk-venue" name="venue_id" required>
              <option value="">Select venue…</option>
              @foreach($venues as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">User</label>
            <select class="form-select" id="bk-user" name="user_id" required>
              <option value="">Select user…</option>
              @foreach($users as $u)
                <option value="{{ $u->id }}">
                  {{ $u->first_name }} {{ $u->last_name }}{{ $u->contact_number ? ' — '.$u->contact_number : '' }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
              <label class="form-label mb-0">Available Slots</label>
              <div class="total-box">Total: ₹<span id="bk-total">0</span></div>
            </div>

            <div id="slotLoading" class="d-none py-3 d-flex align-items-center gap-2">
              <div class="spinner-border spinner-border-sm" role="status"></div>
              <span class="muted-hint">Loading slots…</span>
            </div>

            <div id="slotBox" class="slot-grid mt-2"></div>
            <div id="slotEmptyHint" class="muted-hint d-none">No slots defined for this venue.</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-success" type="submit">Create Booking</button>
      </div>
    </form>
  </div>
</div>

{{-- Edit Booking Modal (no nested forms) --}}
<div class="modal fade" id="editBookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form id="editForm" class="modal-content" method="POST" action="#">
      @csrf
      @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">Edit Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" id="ed-date" name="date" required>
          </div>

          <div class="col-md-8">
            <label class="form-label">Venue</label>
            <select class="form-select" id="ed-venue" name="venue_id" required>
              <option value="">Select venue…</option>
              @foreach($venues as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">User</label>
            <select class="form-select" id="ed-user" name="user_id" required>
              <option value="">Select user…</option>
              @foreach($users as $u)
                <option value="{{ $u->id }}">
                  {{ $u->first_name }} {{ $u->last_name }}{{ $u->contact_number ? ' — '.$u->contact_number : '' }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
              <label class="form-label mb-0">Available Slots</label>
              <div class="total-box">Total: ₹<span id="ed-total">0</span></div>
            </div>

            <div id="slotLoadingEdit" class="d-none py-3 d-flex align-items-center gap-2">
              <div class="spinner-border spinner-border-sm" role="status"></div>
              <span class="muted-hint">Loading slots…</span>
            </div>

            <div id="slotBoxEdit" class="slot-grid mt-2"></div>
            <div id="slotEmptyHintEdit" class="muted-hint d-none">No slots defined for this venue.</div>
          </div>
        </div>
      </div>

      <div class="modal-footer d-flex justify-content-between">
        <button type="button" id="btnDeleteBooking" class="btn btn-outline-danger"
                title="Delete booking">Delete</button>

        <div>
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-success" type="submit" form="editForm">Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</div>
{{-- Hidden delete form (separate from edit form) --}}
<form id="deleteForm" class="d-none" method="POST" action="#">
  @csrf
  @method('DELETE')
</form>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
  // Endpoints (use your route names exactly)
  const routes = {
    events:  @json(route('admin.schedule.events')),
    slots:   @json(route('admin.schedule.slots')),
    show:    @json(route('admin.schedule.show',    ['booking' => '__ID__'])),
    update:  @json(route('admin.schedule.update',  ['booking' => '__ID__'])),
    destroy: @json(route('admin.schedule.destroy', ['booking' => '__ID__'])),
  };

  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const calendarEl = document.getElementById('calendar');
  let calendar, currentEditId = null;

  function startOfDay(d){ const x=new Date(d); x.setHours(0,0,0,0); return x; }
  function todayISO(){ return new Date().toLocaleDateString('en-CA'); } // YYYY-MM-DD

  document.addEventListener('DOMContentLoaded', () => {
    const today = startOfDay(new Date());

    // lock modal date inputs to today+
    const bkDate = document.getElementById('bk-date');
    const edDate = document.getElementById('ed-date');
    bkDate && bkDate.setAttribute('min', todayISO());
    edDate && edDate.setAttribute('min', todayISO());

    calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      selectable: true,
      height: 'auto',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,dayGridWeek'
      },
      buttonText: { today: 'Today', month: 'Month', week: 'Week' },
      events: routes.events,

      // Past days visible but non-selectable
      dayCellClassNames: (arg) => {
        const cellDate = startOfDay(arg.date);
        if (cellDate < today) return ['fc-day-past-disabled'];
        return [];
      },
      dayCellDidMount: (arg) => {
        const cellDate = startOfDay(arg.date);
        if (cellDate < today) arg.el.title = 'Past date (view-only)';
      },

      // Create on future day click
      dateClick: (info) => {
        const clicked = startOfDay(info.date);
        if (clicked < today) return;
        document.getElementById('bk-date').value = info.dateStr;
        document.getElementById('bk-venue').value = '';
        resetSlotsUI('create');
        new bootstrap.Modal(document.getElementById('bookingModal')).show();
      },

      // Edit on event click
      eventClick: (info) => openEditModal(info.event.id)
    });

    calendar.render();

    // Create modal slot loads
    document.getElementById('bk-venue').addEventListener('change', () => tryLoadSlots('create'));
    document.getElementById('bk-date').addEventListener('change',  () => tryLoadSlots('create'));

    // Edit modal slot loads
    document.getElementById('ed-venue').addEventListener('change', () => tryLoadSlots('edit', currentEditId));
    document.getElementById('ed-date').addEventListener('change',  () => tryLoadSlots('edit', currentEditId));

    // Delete button triggers hidden delete form
    document.getElementById('btnDeleteBooking').addEventListener('click', () => {
      if (!currentEditId) return;
      if (!confirm('Delete this booking? This cannot be undone.')) return;
      const form = document.getElementById('deleteForm');
      form.setAttribute('action', routes.destroy.replace('__ID__', currentEditId));
      form.submit();
    });
  });

  // ------- Slots loading helpers -------
  function resetSlotsUI(scope){
    if(scope === 'create'){
      document.getElementById('slotBox').innerHTML = '';
      document.getElementById('bk-total').textContent = '0';
      document.getElementById('slotEmptyHint').classList.add('d-none');
      document.getElementById('slotLoading').classList.add('d-none');
    }else{
      document.getElementById('slotBoxEdit').innerHTML = '';
      document.getElementById('ed-total').textContent = '0';
      document.getElementById('slotEmptyHintEdit').classList.add('d-none');
      document.getElementById('slotLoadingEdit').classList.add('d-none');
    }
  }

  function tryLoadSlots(scope, bookingId = null){
    const venueId = document.getElementById(scope === 'create' ? 'bk-venue' : 'ed-venue').value;
    const date    = document.getElementById(scope === 'create' ? 'bk-date'  : 'ed-date').value;
    if (!venueId || !date) return;

    if(scope === 'create'){
      document.getElementById('slotLoading').classList.remove('d-none');
      document.getElementById('slotBox').innerHTML = '';
      document.getElementById('slotEmptyHint').classList.add('d-none');
    }else{
      document.getElementById('slotLoadingEdit').classList.remove('d-none');
      document.getElementById('slotBoxEdit').innerHTML = '';
      document.getElementById('slotEmptyHintEdit').classList.add('d-none');
    }

    fetch(`${routes.slots}?venue_id=${encodeURIComponent(venueId)}&date=${encodeURIComponent(date)}`)
      .then(r => r.json())
      .then(slots => {
        if(scope === 'create'){
          renderSlotsCreate(slots);
        }else{
          const selected = document.getElementById('slotBoxEdit').dataset.selectedSlotId || null;
          renderSlotsEdit(slots, selected);
        }
      })
      .catch(() => resetSlotsUI(scope));
  }

  function renderSlotsCreate(slots){
    const box = document.getElementById('slotBox');
    document.getElementById('slotLoading').classList.add('d-none');
    box.innerHTML = '';
    document.getElementById('bk-total').textContent = '0';

    if (!slots || slots.length === 0){
      document.getElementById('slotEmptyHint').classList.remove('d-none');
      return;
    }

    slots.forEach(s => {
      const card = document.createElement('label');
      card.className = `slot-card ${s.is_booked ? 'booked' : ''}`;
      card.innerHTML = `
        <input class="form-check-input slot-radio" type="radio" name="slot_id"
               value="${s.id}" ${s.is_booked ? 'disabled' : ''} data-price="${s.price}">
        <div>
          <div class="slot-title">${s.name}</div>
          <div class="slot-meta">${(s.start || '').substring(0,5)} – ${(s.end || '').substring(0,5)}
            ${s.full_time ? '<span class="slot-badge">Full Day</span>' : ''}
            ${s.full_venue ? '<span class="slot-badge">Full Venue</span>' : ''}
          </div>
        </div>
        <div class="ms-auto slot-price">₹${s.price}</div>
      `;
      box.appendChild(card);
    });

    box.querySelectorAll('input.slot-radio').forEach(r => {
      r.addEventListener('change', (e) => {
        const price = parseInt(e.target.dataset.price || '0', 10);
        document.getElementById('bk-total').textContent = isNaN(price) ? '0' : price.toString();
      });
    });
  }

  function renderSlotsEdit(slots, selectedSlotId){
    const box = document.getElementById('slotBoxEdit');
    document.getElementById('slotLoadingEdit').classList.add('d-none');
    box.innerHTML = '';
    document.getElementById('ed-total').textContent = '0';

    if (!slots || slots.length === 0){
      document.getElementById('slotEmptyHintEdit').classList.remove('d-none');
      return;
    }

    slots.forEach(s => {
      const checked = String(s.id) === String(selectedSlotId) ? 'checked' : '';
      const disabled = s.is_booked && !checked ? 'disabled' : ''; // keep current slot selectable
      const card = document.createElement('label');
      card.className = `slot-card ${s.is_booked && !checked ? 'booked' : ''}`;
      card.innerHTML = `
        <input class="form-check-input slot-radio-ed" type="radio" name="slot_id"
               value="${s.id}" ${disabled} ${checked} data-price="${s.price}">
        <div>
          <div class="slot-title">${s.name}</div>
          <div class="slot-meta">${(s.start || '').substring(0,5)} – ${(s.end || '').substring(0,5)}
            ${s.full_time ? '<span class="slot-badge">Full Day</span>' : ''}
            ${s.full_venue ? '<span class="slot-badge">Full Venue</span>' : ''}
          </div>
        </div>
        <div class="ms-auto slot-price">₹${s.price}</div>
      `;
      box.appendChild(card);
    });

    const pre = box.querySelector('input.slot-radio-ed:checked');
    if (pre) document.getElementById('ed-total').textContent = pre.dataset.price || '0';

    box.querySelectorAll('input.slot-radio-ed').forEach(r => {
      r.addEventListener('change', (e) => {
        const price = parseInt(e.target.dataset.price || '0', 10);
        document.getElementById('ed-total').textContent = isNaN(price) ? '0' : price.toString();
      });
    });
  }

  // ------- Edit flow -------
  function openEditModal(id){
    currentEditId = id;
    const showUrl = routes.show.replace('__ID__', id);

    fetch(showUrl, { headers: { 'Accept': 'application/json' }})
      .then(async r => {
        if (!r.ok) {
          const txt = await r.text();
          throw new Error(`HTTP ${r.status}: ${txt.slice(0,200)}`);
        }
        return r.json();
      })
      .then(data => {
        const editForm = document.getElementById('editForm');
        const deleteForm = document.getElementById('deleteForm');
        if (!editForm || !deleteForm) throw new Error('Edit/Delete form not found in DOM.');

        // Set form actions
        editForm.setAttribute('action',  routes.update.replace('__ID__', id));
        deleteForm.setAttribute('action', routes.destroy.replace('__ID__', id));

        // Fill fields
        document.getElementById('ed-date').value  = data.date;
        document.getElementById('ed-venue').value = data.venue_id;
        document.getElementById('ed-user').value  = data.user_id;

        // Preserve selected slot
        const holder = document.getElementById('slotBoxEdit');
        holder.dataset.selectedSlotId = data.slot_id ?? '';

        // Load slots for that day/venue with preselection
        tryLoadSlots('edit', id);

        new bootstrap.Modal(document.getElementById('editBookingModal')).show();
      })
      .catch(err => {
        alert('Failed to load booking details.\n\n' + err.message);
      });
  }
</script>
@endsection
