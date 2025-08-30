@extends('shared.layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
    <style>
  .dashboard-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:12px}
  .dashboard-card{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(64,81,137,.07),0 1.5px 4px rgba(60,72,100,.05);padding:26px 22px 20px;position:relative;min-height:140px;display:flex;flex-direction:column;justify-content:flex-start;transition:box-shadow .2s}
  .dashboard-card:hover{box-shadow:0 4px 20px rgba(64,81,137,.14),0 2.5px 6px rgba(60,72,100,.09)}
  .dashboard-card h1{font-size:1.05rem;color:#454545;font-weight:500;margin-bottom:18px}
  .dashboard-card h2{font-size:2rem;font-weight:600;color:#282828;margin-bottom:10px}
  .dashboard-card.revenue{border-bottom:3px solid #2a5eff24}
  .dashboard-card.bookings{border-bottom:3px solid #14b67224}
  .dashboard-card.workshops{border-bottom:3px solid #fe3f5222}
  @media (max-width:900px){.dashboard-grid{grid-template-columns:1fr}}

  /* ---------------- Actions column (fixed) ---------------- */
  /* Keep <td> as a real table-cell so borders render correctly */
  .actions-cell{
    padding-top:.625rem;
    padding-bottom:.625rem;
    vertical-align: middle;
    text-align: right;
    white-space: nowrap;            /* keep icons on one line */
  }

  /* Ensure table row separators are consistent across all cells */
  .table.align-middle>tbody>tr>td{
    border-bottom:1px solid var(--bs-table-border-color);
  }

  /* Make the form inline so it doesn't force a line break */
  .action-form{display:inline-block;margin:0;padding:0}

  /* Icon buttons */
  .btn-icon{
    margin:0;
    width:36px;height:36px;border:1px solid #E5E7EB;border-radius:8px;
    display:inline-flex;align-items:center;justify-content:center;
    vertical-align:middle;          /* align with neighbors */
    background:#fff;color:#111827;
    transition:transform .12s ease, background .12s ease, border-color .12s ease
  }
  .btn-icon svg{width:18px;height:18px}
  .btn-icon:hover{transform:translateY(-1px)}
  .btn-icon:focus{outline:none;box-shadow:0 0 0 3px rgba(37,99,235,.15)}
  .btn-icon[disabled],.btn-icon.disabled{opacity:.55;cursor:not-allowed;transform:none!important}

  .btn-items{border-color:#DBEAFE;background:#EFF6FF;color:#1D4ED8}
  .btn-items:hover{background:#E0F2FE}
  .btn-cashback{border-color:#CFF8E5;background:#ECFDF5;color:#059669}
  .btn-cashback:hover{background:#D1FAE5}
  .btn-delete{border-color:#FECACA;background:#FEF2F2;color:#DC2626}
  .btn-delete:hover{background:#FEE2E2}

  /* ---------------- Items offcanvas width ---------------- */
  #itemsOffcanvas{--bs-offcanvas-width:720px}
  @media (min-width:1200px){#itemsOffcanvas{--bs-offcanvas-width:840px}} /* desktops */
  @media (max-width:576px){#itemsOffcanvas{--bs-offcanvas-width:100vw}} /* phones: full width */
</style>

@endsection

@section('title', 'Bookings')

@section('content')
    <div class="main-content pt-0">
        <br>

        <!-- breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">
                <a href="{{ route('admin.bookings') }}" style="color: inherit; text-decoration: none;">Bookings</a>
            </div>
        </div>

        <!-- Filters -->
        @php $hasFilters = request()->filled('search') || request()->filled('date_filter'); @endphp
        <form method="GET" action="{{ route('admin.bookings') }}" class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control" style="width:200px"
                placeholder="Search bookings…">
            <select name="date_filter" class="form-select" style="width:160px" onchange="this.form.submit()">
                <option value="">All Dates</option>
                <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                <option value="this_year" {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>This Year</option>
            </select>

            @if ($hasFilters)
                <a href="{{ route('admin.bookings') }}" class="btn btn-outline-secondary">Clear</a>
            @endif

            <a href="{{ route('admin.bookings.export', request()->only('search', 'date_filter', 'venue_id', 'status')) }}"
                class="btn btn-outline-secondary ms-auto">Export</a>
        </form>

        <!-- bookings table -->
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Venue</th>
                        <th>Time-slot</th>
                        <th>Booking Date</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Booked on</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)

                        @if ($booking->user->role === 'admin')
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>Admin</td>
                                <td>{{ $booking->venue->name }}</td>
                                <td>{{ $booking->timeSlot->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</td>
                                <td colspan="4" class="text-center"><span class="badge bg-danger">Bookings
                                        Unavailable</span></td>
                            </tr>
                        @else
                            @php
                                $canCashback = \Carbon\Carbon::parse($booking->booking_date)->lt(now());
                                $itemsTotal = (float) ($booking->items_total ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>{{ $booking->user->first_name }} {{ $booking->user->last_name }}</td>
                                <td>{{ $booking->venue->name }}</td>
                                <td>
                                    {{ $booking->timeSlot->name }}
                                    @if ($booking->full_time)
                                        <span class="badge bg-info" style="font-size: .75rem;">Full Day</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</td>

                                <td>
                                    ₹{{ number_format($booking->payment->amount) }}
                                    @if ($itemsTotal > 0)
                                        <div class="text-muted small">+ Items ₹{{ number_format($itemsTotal, 2) }}</div>
                                    @endif
                                </td>

                                <td>
                                    @if ($booking->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($booking->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>

                                <td>{{ $booking->created_at->format('d M, Y H:i') }}</td>

                                <td class="actions-cell">
                                    {{-- Items Manager --}}
                                    <button type="button" class="btn-icon btn-items open-items-offcanvas"
                                        data-bs-toggle="tooltip" data-bs-title="Add/Edit items"
                                        data-booking-id="{{ $booking->id }}"
                                        data-booking-label="#{{ $booking->id }} · {{ $booking->user->first_name }} · {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}">
                                        <!-- box svg -->
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 2 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                            <path d="M3.3 7L12 12l8.7-5M12 22V12" />
                                        </svg>
                                    </button>

                                    {{-- Cashback --}}
                                    {{-- @if ($canCashback) --}}
                                        <a href="{{ route('admin.money-back.create') }}?booking={{ $booking->id }}"
                                            class="btn-icon btn-cashback" data-bs-toggle="tooltip"
                                            data-bs-title="Cashback / Money Back">
                                            <!-- rupee svg -->
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M7 4h10M7 9h10M7 4a5 5 0 0 0 5 5c-3 6-5 7-5 7h10" />
                                            </svg>
                                        </a>
                                    {{-- @else --}}
                                        {{-- <button type="button" class="btn-icon btn-cashback disabled"
                                            data-bs-toggle="tooltip" data-bs-title="Available after booking date">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M7 4h10M7 9h10M7 4a5 5 0 0 0 5 5c-3 6-5 7-5 7h10" />
                                            </svg>
                                        </button> --}}
                                    {{-- @endif --}}

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST"
                                        class="action-form d-inline-block"
                                        onsubmit="return confirm('Delete booking #{{ $booking->id }}? This cannot be undone.');">
                                        @csrf @method('DELETE')
                                        <button class="btn-icon btn-delete" type="submit" data-bs-toggle="tooltip"
                                            data-bs-title="Delete booking">
                                            <!-- trash svg -->
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                                <path d="M10 11v6M14 11v6" />
                                                <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                No bookings found{{ $hasFilters ? ' for current filter' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- pagination --}}
            @include('components.pagination', ['paginator' => $bookings])
        </div>
    </div>

    {{-- ===================== Items Manager Offcanvas ===================== --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="itemsOffcanvas" aria-labelledby="itemsOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="itemsOffcanvasLabel">Booking Items</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <div class="mb-2 text-muted" id="itemsBookingMeta"></div>

            <div class="table-responsive">
                <table class="table align-middle" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:38%">Item</th>
                            <th style="width:18%">Qty</th>
                            <th style="width:22%">Unit Price (₹)</th>
                            <th style="width:18%">Total (₹)</th>
                            <th style="width:4%"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <button class="btn btn-outline-primary btn-sm mb-3" id="addItemRow">
                <span class="align-text-bottom" style="display:inline-flex;vertical-align:middle">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                </span>
                Add item
            </button>

            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                <div class="text-muted">Items Subtotal</div>
                <div class="fs-5 fw-semibold" id="itemsSubtotal">₹0</div>
            </div>

            <div class="d-grid mt-3">
                <button class="btn btn-primary" id="saveItemsBtn">
                    <span class="align-text-bottom me-1" style="display:inline-flex;vertical-align:middle">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <path d="M17 21V13H7v8" />
                            <path d="M7 3v5h8" />
                        </svg>
                    </span>
                    Save items
                </button>
            </div>
        </div>
    </div>

    <template id="itemRowTpl">
        <tr>
            <td>
                <input type="text" class="form-control item-name" placeholder="Chair / Fan / Decoration…">
                <input type="hidden" class="item-id">
            </td>
            <td><input type="number" min="1" step="1" class="form-control item-qty" value="1"></td>
            <td><input type="number" min="0" step="0.01" class="form-control item-price" value="0">
            </td>
            <td class="item-total fw-semibold">0</td>
            <td>
                <button class="btn btn-light btn-sm text-danger remove-row" title="Remove">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </td>
        </tr>
    </template>
@endsection

@section('js')
    <script>
        (function() {
            // Enable Bootstrap tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

            // Items Offcanvas logic
            let currentBookingId = null;
            let deletedIds = new Set();

            const offcanvasEl = document.getElementById('itemsOffcanvas');
            const bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);
            const itemsTable = document.getElementById('itemsTable');
            const itemsTbody = itemsTable.querySelector('tbody');
            const tpl = document.getElementById('itemRowTpl');
            const subtotalEl = document.getElementById('itemsSubtotal');
            const metaEl = document.getElementById('itemsBookingMeta');
            const addBtn = document.getElementById('addItemRow');
            const saveBtn = document.getElementById('saveItemsBtn');

            // Open from any Items button
            document.querySelectorAll('.open-items-offcanvas').forEach(btn => {
                btn.addEventListener('click', async () => {
                    currentBookingId = btn.dataset.bookingId;
                    metaEl.textContent = btn.dataset.bookingLabel || '';
                    deletedIds = new Set();
                    itemsTbody.innerHTML = '';
                    subtotalEl.textContent = '₹0';

                    const res = await fetch(
                        `{{ url('admin/bookings') }}/${currentBookingId}/items`);
                    if (res.ok) {
                        const data = await res.json();
                        (data.items || []).forEach(addRowFromData);
                        updateSubtotal();
                    }

                    bsOffcanvas.show();
                });
            });

            addBtn.addEventListener('click', () => {
                addRowFromData();
                updateSubtotal();
            });

            function addRowFromData(item = null) {
                const node = tpl.content.cloneNode(true);
                const tr = node.querySelector('tr');
                const id = tr.querySelector('.item-id');
                const name = tr.querySelector('.item-name');
                const qty = tr.querySelector('.item-qty');
                const price = tr.querySelector('.item-price');
                const total = tr.querySelector('.item-total');

                if (item) {
                    id.value = item.id || '';
                    name.value = item.name || '';
                    qty.value = item.qty ?? 1;
                    price.value = item.unit_price ?? 0;
                    total.textContent = Number(item.total || 0).toFixed(2);
                }

                function recalc() {
                    const t = Number(qty.value || 0) * Number(price.value || 0);
                    total.textContent = t.toFixed(2);
                    updateSubtotal();
                }
                qty.addEventListener('input', recalc);
                price.addEventListener('input', recalc);

                tr.querySelector('.remove-row').addEventListener('click', () => {
                    if (id.value) deletedIds.add(Number(id.value));
                    tr.remove();
                    updateSubtotal();
                });

                itemsTbody.appendChild(node);
            }

            function updateSubtotal() {
                let sum = 0;
                itemsTbody.querySelectorAll('.item-total').forEach(td => sum += Number(td.textContent || 0));
                subtotalEl.textContent = '₹' + sum.toFixed(2);
            }

            saveBtn.addEventListener('click', async () => {
                saveBtn.disabled = true;

                const payload = {
                    items: [],
                    deleted_ids: Array.from(deletedIds)
                };
                itemsTbody.querySelectorAll('tr').forEach(tr => {
                    const row = {
                        id: tr.querySelector('.item-id').value || null,
                        name: tr.querySelector('.item-name').value.trim(),
                        qty: Number(tr.querySelector('.item-qty').value || 0),
                        unit_price: Number(tr.querySelector('.item-price').value || 0),
                    };
                    if (row.name && row.qty > 0) payload.items.push(row);
                });

                const res = await fetch(
                    `{{ url('admin/bookings') }}/${currentBookingId}/items/bulk-upsert`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });

                if (res.ok) {
                    bsOffcanvas.hide();
                    location.reload(); // reflect items subtotal in list
                } else {
                    alert('Could not save items. Please check the values and try again.');
                    saveBtn.disabled = false;
                }
            });
        })();
    </script>
@endsection
