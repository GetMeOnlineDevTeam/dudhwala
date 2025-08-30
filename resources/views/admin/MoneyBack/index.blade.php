@extends('shared.layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
    <style>
        .custom-input-icon {
            position: relative
        }

        .custom-input-icon input {
            padding-left: 2rem
        }

        .custom-input-icon .material-icons {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 1.2rem
        }

        .badge-refund {
            background: #ffe5e8;
            color: #b4232c;
            border: 1px solid #ffccd1
        }

        .badge-return {
            background: #eef7f1;
            color: #116631;
            border: 1px solid #cfe9db
        }

        .table td,
        .table th {
            vertical-align: middle
        }
    </style>
@endsection

@section('title', 'Money Back')

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
            <div class="ps-2"></div>
        </div>

        @php
            $hasFilters = request()->filled('q') || request()->filled('type');
            $typeOptions = ['refund' => 'Refund', 'return' => 'Return'];
        @endphp

        {{-- Filters --}}
        <div class="row g-3">
            <form method="GET" action="{{ route('admin.money-back.index') }}" class="d-flex flex-wrap gap-2 mb-4">
                {{-- Search --}}
                <div class="position-relative custom-input-icon">
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                        style="width:230px" placeholder="Search (ID / amount / user / venue / slot)…">
                </div>

                {{-- Type --}}
                <select name="type" class="form-select" style="width:150px" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    @foreach ($typeOptions as $val => $label)
                        <option value="{{ $val }}" @selected(request('type') === $val)>{{ $label }}</option>
                    @endforeach
                </select>

                @if ($hasFilters)
                    <a href="{{ route('admin.money-back.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Venue</th>
                        <th>Time Slot</th>
                        <th>Type</th>
                        <th>Processed</th>
                        <th>Amount</th>
                        <th>Status</th>

                    </tr>
                </thead>
                <tbody>
                    @forelse($moneyBacks as $moneyBack)
                        @php
                            // When booking is deleted (refund), these may be null.
                            $venueName =
                                $moneyBack->booking && $moneyBack->booking->venue
                                    ? $moneyBack->booking->venue->name
                                    : '—';

                            $slotName =
                                $moneyBack->booking && $moneyBack->booking->timeSlot
                                    ? $moneyBack->booking->timeSlot->name
                                    : '—';

                            $processed = $moneyBack->processed_at
                                ? $moneyBack->processed_at->format('d M Y, H:i')
                                : $moneyBack->created_at->format('d M Y, H:i');

                            $type = strtolower($moneyBack->type);
                            $badgeCls = $type === 'refund' ? 'badge-refund' : 'badge-return';

                            // Prefer the MoneyBack->user (persisted even if booking is deleted)
                            $userName = $moneyBack->user
                                ? $moneyBack->user->first_name . ' ' . $moneyBack->user->last_name
                                : '—';
                        @endphp
                        <tr>
                            <td>{{ $moneyBack->id }}</td>
                            <td>{{ $userName }}</td>
                            <td>{{ $venueName }}</td>
                            <td>{{ $slotName }}</td>
                            <td><span class="badge {{ $badgeCls }}">{{ ucfirst($type) }}</span></td>
                            <td class="text-muted">{{ $processed }}</td>
                            <td>₹{{ number_format($moneyBack->amount, 2) }}</td>
                            <td>
    <form action="{{ route('admin.money-back.update-status', $moneyBack->id) }}" method="POST">
        @csrf
        @method('PATCH') <!-- Use PATCH method to update existing record -->
        
        <div class="d-flex justify-content-start align-items-center">
            <select name="status" class="form-select form-select-sm status-select" onchange="this.form.submit()">
                <option value="success" @selected($moneyBack->status === 'success')>Success</option>
                <option value="pending" @selected($moneyBack->status === 'pending')>Pending</option>
                <option value="processing" @selected($moneyBack->status === 'processing')>Processing</option>
            </select>
        </div>
    </form>
</td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-search fs-3 d-block mb-2"></i>
                                No money-back entries found{{ $hasFilters ? ' for current filter' : '' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @include('components.pagination', ['paginator' => $moneyBacks])
        </div>
    </div>
@endsection
