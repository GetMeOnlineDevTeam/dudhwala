@extends('shared.layouts.app')

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<style>
  :root{
    --brand:#116631; --brand-dark:#0e5428;
    --gray-900:#111827; --gray-800:#1f2937; --gray-700:#374151;
  }
  .calendar-card{border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(64,81,137,.07),0 1.5px 4px rgba(60,72,100,.05)}
  .calendar-card .card-header{background:linear-gradient(90deg,#f8fafc,#f3f4f6)}
  .fc .fc-toolbar-title{font-size:1.05rem;font-weight:600;color:var(--gray-800)}
  .fc .fc-col-header-cell-cushion{color:var(--gray-700)}
  .fc .fc-daygrid-day-number{color:var(--gray-900)}
  .fc a{color:inherit;text-decoration:none}
  .fc .fc-button{border-radius:10px}
  .fc .fc-button,.fc .fc-button-primary{background-color:var(--brand);border-color:var(--brand);color:#fff}
  .fc .fc-button:hover,.fc .fc-button:focus,.fc .fc-button-primary:not(:disabled).fc-button-active{background-color:var(--brand-dark);border-color:var(--brand-dark);color:#fff}
  .fc .fc-button:disabled{opacity:.65;background-color:var(--brand);border-color:var(--brand);color:#fff}
  .fc-day-past-disabled{opacity:.45}
  .btn-success{background-color:var(--brand)!important;border-color:var(--brand)!important}
  .btn-success:hover{background-color:var(--brand-dark)!important;border-color:var(--brand-dark)!important}
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
@can('schedule.create')
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

          

          
          {{-- Slots removed --}}
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-success" type="submit">Create Booking</button>
      </div>
    </form>
  </div>
</div>
@endcan
@can('schedule.edit')
{{-- Edit Booking Modal (no user change, no slots) --}}
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

          

          {{-- User + Slots removed from EDIT --}}
        </div>
      </div>

      <div class="modal-footer d-flex justify-content-between">
        <div>
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-success" type="submit" form="editForm">Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</div>

@endcan

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
  const routes = {
    events:  @json(route('admin.schedule.events')),
    show:    @json(route('admin.schedule.show',    ['booking' => '__ID__'])),
    update:  @json(route('admin.schedule.update',  ['booking' => '__ID__'])),
    destroy: @json(route('admin.schedule.destroy', ['booking' => '__ID__'])),
  };

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
      headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,dayGridWeek' },
      buttonText: { today:'Today', month:'Month', week:'Week' },
      events: routes.events,

      dayCellClassNames: (arg) => (startOfDay(arg.date) < today ? ['fc-day-past-disabled'] : []),

      dateClick: (info) => {
        const clicked = startOfDay(info.date);
        if (clicked < today) return;
        document.getElementById('bk-date').value = info.dateStr;
        new bootstrap.Modal(document.getElementById('bookingModal')).show();
      },

      eventClick: (info) => openEditModal(info.event.id)
    });

    calendar.render();

   
  });

  // ------- Edit flow (no user/slot changes) -------
  function openEditModal(id){
    currentEditId = id;
    const showUrl = routes.show.replace('__ID__', id);

    fetch(showUrl, { headers: { 'Accept': 'application/json' }})
      .then(async r => { if(!r.ok){ throw new Error(`HTTP ${r.status}`) } return r.json(); })
      .then(data => {
        const editForm  = document.getElementById('editForm');
        editForm.setAttribute('action',  routes.update.replace('__ID__', id));

        document.getElementById('ed-date').value  = data.date;

        new bootstrap.Modal(document.getElementById('editBookingModal')).show();
      })
      .catch(err => alert('Failed to load booking details.\n\n' + err.message));
  }
</script>
@endsection
