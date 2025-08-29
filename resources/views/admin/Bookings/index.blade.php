@extends('shared.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin-top: 12px;
    }

    .dashboard-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(64, 81, 137, 0.07),
            0 1.5px 4px rgba(60, 72, 100, 0.05);
        padding: 26px 22px 20px;
        position: relative;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        transition: box-shadow .2s;
    }

    .dashboard-card:hover {
        box-shadow: 0 4px 20px rgba(64, 81, 137, 0.14),
            0 2.5px 6px rgba(60, 72, 100, 0.09);
    }

    .dashboard-card h1 {
        font-size: 1.05rem;
        color: #454545;
        font-weight: 500;
        margin-bottom: 18px;
    }

    .dashboard-card h2 {
        font-size: 2rem;
        font-weight: 600;
        color: #282828;
        margin-bottom: 10px;
    }

    .dashboard-card.revenue {
        border-bottom: 3px solid #2a5eff24;
    }

    .dashboard-card.bookings {
        border-bottom: 3px solid #14b67224;
    }

    .dashboard-card.workshops {
        border-bottom: 3px solid #fe3f5222;
    }

    @media (max-width: 900px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }



    /* … your existing dashboard/grid/card styles … */

    /* Action buttons styling */
    .actions-cell {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        /* center icons vertically */
        gap: 6px;
        height: 100%;
        /* span full row height */
    }

    .action-btn {
        background: transparent;
        border: 1px solid;
        border-radius: 4px;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
    }

    .action-btn.edit {
        color: #14b672;
        border-color: #14b672;
    }

    .action-btn.edit:hover {
        background: rgba(20, 182, 114, 0.1);
    }

    .action-btn.delete {
        color: #fe3f52;
        border-color: #fe3f52;
    }

    .action-btn.delete:hover {
        background: rgba(254, 63, 82, 0.1);
    }

    .action-form {
        margin: 0;
    }
</style>
@endsection

@section('title', 'Bookings')

@section('content')
<div class="main-content pt-0">
    <br>

    <!-- breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">
            <a href="{{ route('admin.bookings') }}" style="color: inherit; text-decoration: none;">
                Bookings
            </a>
        </div>
    </div>
    <!-- end breadcrumb -->

    <!-- Filters -->
    @php
    $hasFilters = request()->filled('search') || request()->filled('date_filter');
    @endphp
    <form method="GET" action="{{ route('admin.bookings') }}" class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            class="form-control"
            style="width:200px"
            placeholder="Search bookings…">

        <select
            name="date_filter"
            class="form-select"
            style="width:160px"
            onchange="this.form.submit()">
            <option value="">All Dates</option>
            <option value="today" {{ request('date_filter')==='today'    ? 'selected' : '' }}>Today</option>
            <option value="this_week" {{ request('date_filter')==='this_week'? 'selected' : '' }}>This Week</option>
            <option value="this_year" {{ request('date_filter')==='this_year'? 'selected' : '' }}>This Year</option>
        </select>

        @if($hasFilters)
        <a href="{{ route('admin.bookings') }}" class="btn btn-outline-secondary">Clear</a>
        @endif

        <a href="{{ route('admin.bookings.export', request()->only('search','date_filter','venue_id','status')) }}" class="btn btn-outline-secondary ms-auto">Export</a>
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

                @if ($booking->user->role == 'admin')
                <tr>
                    <td>{{ $booking->id }}</td>
                    <td>{{ 'Admin' }}</td>
                    <td>{{ $booking->venue->name }}</td>
                    <td>{{ $booking->timeSlot->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</td>
                    <td colspan=4 align="center"><span class="badge bg-danger">Bookings Unavailable</span></td>
                </tr>
                @else
                <tr>
                    <td>{{ $booking->id }}</td>
                    <td>{{ $booking->user->first_name }} {{ $booking->user->last_name }}</td>
                    <td>{{ $booking->venue->name }}</td>
                    <td>
                        {{ $booking->timeSlot->name }}
                        @if($booking->full_time)
                        <span class="badge bg-info" style="font-size: small;">Full Day</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</td>
                    <td>₹{{ number_format($booking->payment->amount) }}</td>
                    <td>
                        @if($booking->status === 'approved')
                        <span class="badge bg-success">Approved</span>
                        @elseif($booking->status === 'pending')
                        <span class="badge bg-warning">Pending</span>
                        @else
                        <span class="badge bg-danger">Cancelled</span>
                        @endif
                    </td>

                    <td>
                        {{ $booking->created_at->format('d M, Y H:i') }}
                    </td>

                    <td>
                        {{-- Cashback (only after the booking date) --}}
                        @if($booking->booking_date < now())
                            <a
                            href="{{ route('admin.money-back.create') }}?booking={{ $booking->id }}"
                            class="action-btn edit"
                            title="Cashback / Money Back">
                            <span class="material-icons-outlined">attach_money</span>
                            </a>
                            @endif

                            {{-- Delete (unchanged) --}}

                            <a
                                href="{{ route('admin.money-back.create') }}?booking={{ $booking->id }}"
                                class="action-btn delete"
                                title="Cashback / Money Back">
                                <span class="material-icons-outlined">delete</span>
                            </a>

                    </td>
                    @endif
                </tr>
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
@endsection

@section('js')

@endsection