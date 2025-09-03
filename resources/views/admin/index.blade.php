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
            box-shadow: 0 2px 8px rgba(64, 81, 137, 0.07), 0 1.5px 4px rgba(60, 72, 100, 0.05);
            padding: 26px 22px 20px 22px;
            position: relative;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            transition: box-shadow .2s;
        }

        .dashboard-card:hover {
            box-shadow: 0 4px 20px rgba(64, 81, 137, 0.14), 0 2.5px 6px rgba(60, 72, 100, 0.09);
        }

        .dashboard-card h1 {
            font-size: 1.05rem;
            color: #454545;
            font-weight: 500;
            margin-bottom: 18px;
            letter-spacing: 0.01em;
        }

        .dashboard-card h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #282828;
            margin: 0;
            margin-bottom: 10px;
        }

        .dashboard-metrics {
            display: flex;
            align-items: baseline;
            gap: 12px;
        }

        .dashboard-increase {
            font-size: 1rem;
            color: #14b672;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .dashboard-percent {
            font-size: 0.95rem;
        }

        /* Add bottom border color to each card for accent */
        .dashboard-card.revenue {
            border-bottom: 3px solid #2a5eff24;
        }

        .dashboard-card.bookings {
            border-bottom: 3px solid #14b67224;
        }

        .dashboard-card.workshops {
            border-bottom: 3px solid #fe3f5222;
        }

        /* Responsive for mobile */
        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection


@section('title', 'banner')
@section('content')


    <div class="main-content pt-0">

        <br>

        <div class="dashboard-grid">
            <div class="dashboard-card revenue">
                <h1>Total Revenue</h1>
                <div class="dashboard-metrics">
                    <h2>₹{{ number_format($netRevenue) }}</h2>
                </div>
            </div>

            <div class="dashboard-card bookings">
                <h1>Total Users</h1>
                <div class="dashboard-metrics">
                    <h2>{{ count($users) }}</h2>
                </div>
            </div>

            <div class="dashboard-card workshops">
                <h1>Total Bookings</h1>
                <div class="dashboard-metrics">
                    <h2>{{ count($bookings) }}</h2>
                </div>
            </div>
        </div>

        <br>
        <br>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Users</h3>
            <a href="{{ route('admin.users') }}" class="btn btn-sm px-4 py-2 fw-semibold"
                style="background-color: #116631; color: white; border-radius: 5px;">
                View All
            </a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Document Verification</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                            <td>{{ $user->contact_number }}</td>
                            <td>
                                @if ($user->is_verified == true)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user->id) }}">
                                    <span class="material-icons-outlined">visibility</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @include('components.pagination', ['paginator' => $users])
        </div>

        <br>
        <br>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Recent Bookings</h3>
            <a href="{{ route('admin.bookings') }}" class="btn btn-sm px-4 py-2 fw-semibold"
                style="background-color: #116631; color: white; border-radius: 5px;">
                View All
            </a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
    <thead class="table-light">
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Name</th>
            <th scope="col">Community</th>
            <th scope="col">Venue</th>
            <th scope="col">Time-slot</th>
            <th scope="col">Booking Date</th>
            <th scope="col">Discount</th>
            <th scope="col">Payment</th>
            <th scope="col">Status</th>
        </tr>
    </thead>

    <tbody>
        @forelse($bookings as $booking)
            <tr>
                <td>{{ $booking->id }}</td>

                <td>{{ $booking->user->first_name }} {{ $booking->user->last_name }}</td>

                <td>
                    @php $comm = strtolower($booking->community ?? 'non-dudhwala'); @endphp
                    <span class="badge {{ $comm === 'dudhwala' ? 'bg-success' : 'bg-secondary' }}">
                        {{ ucfirst($comm) }}
                    </span>
                </td>

                <td>{{ $booking->venue->name ?? '—' }}</td>

                <td>
                    {{ $booking->timeSlot->name ?? '—' }}
                    @if ($booking->full_time)
                        <br><span class="badge bg-info" style="font-size: small;">Full Day</span>
                    @endif
                </td>

                <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</td>

                <td>
                    @php $disc = (int) ($booking->discount ?? 0); @endphp
                    @if($disc > 0)
                        <span class="text-success">− ₹{{ number_format($disc) }}</span>
                    @else
                        —
                    @endif
                </td>

                <td>
                    ₹{{ number_format((float) ($booking->payment?->amount ?? 0)) }}
                </td>

                <td>
                    @php
                        $s = strtolower(trim($booking->status ?? ''));
                        switch ($s) {
                            case 'confirmed':
                                $badge = 'badge bg-success';
                                $label = $s === 'paid' ? 'Paid' : 'Confirmed';
                                break;

                            case 'pending':
                                $badge = 'badge bg-warning';
                                $label = 'Pending';
                                break;

                            case 'cancelled':
                                $badge = 'badge bg-danger';
                                $label = 'Cancelled';
                                break;

                            default:
                                $badge = 'badge bg-secondary';
                                $label = $booking->status ? ucfirst($booking->status) : 'Unknown';
                        }
                    @endphp
                    <span class="{{ $badge }}">{{ $label }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No bookings found</td>
            </tr>
        @endforelse
    </tbody>
</table>

            @include('components.pagination', ['paginator' => $bookings])
        </div>




    </div>
@endsection

@section('js')
@endsection
