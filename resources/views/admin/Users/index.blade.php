@extends('shared.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
<style>
    .status-tabs .nav-link {
        color: #116631;
        font-weight: 500;
        border: none;
        background: transparent;
        padding: 0 8px;
        font-size: 1rem;
        transition: color 0.15s;
    }

    .status-tabs .nav-link.active,
    .status-tabs .nav-link:focus,
    .status-tabs .nav-link:hover {
        color: #0d3992;
        text-decoration: underline;
        background: transparent;
    }

    .custom-input-icon {
        position: relative;
    }

    .custom-input-icon input {
        padding-left: 2rem;
    }

    .custom-input-icon .material-icons {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
        font-size: 1.2rem;
    }
</style>
@endsection

@section('title', 'Users')

@section('content')
<div class="main-content pt-0">
    <br>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">
            <a href="{{ route('admin.users') }}" style="color: inherit; text-decoration: none;">
                Users
            </a>
        </div>
        <div class="">

        </div>
    </div>
    <!--end breadcrumb-->

    @php $hasFilters = request()->filled('search') || request()->filled('verification'); @endphp

    <div class="row g-3">
        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.users') }}" class="d-flex flex-wrap gap-2 mb-4">
            <div class="position-relative">
                <input type="search" name="search" value="{{ request('search') }}"
                    class="form-control ps-4" style="width:200px"
                    placeholder="Search user…">

            </div>

            <select name="verification" class="form-select" style="width:140px" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="1" {{ request('verification')==='1'?'selected':'' }}>
                    Verified
                </option>
                <option value="0" {{ request('verification')==='0'?'selected':'' }}>
                    Not Verified
                </option>
            </select>

            @if(request('search') || request('verification')!==null)
            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                Clear
            </a>
            @endif

            <a href="{{ route('admin.users.export', request()->only('search','verification')) }}" class="btn btn-outline-secondary ms-auto">Export</a>
        </form>
    </div>

    <div class="table-responsive">
        <br>
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Contact</th>
                    {{-- <th>Document Verification</th> --}}
                    {{-- <th>Actions</th> --}}
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                    <td>{{ $user->contact_number }}</td>
                    {{-- <td>
                        @if($user->is_verified == true)
                        <span class="badge bg-success">Verified</span>
                        @else
                        <span class="badge bg-danger">Not Verified</span>
                        @endif
                    </td> --}}
                    {{-- <td>
                        <a href="{{ route('admin.users.edit', $user->id) }}" >
                            <span class="material-icons-outlined">visibility</span>
                        </a>
                    </td> --}}
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        No users found{{ $hasFilters ? ' for current filter' : '' }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @include('components.pagination', ['paginator' => $users])
    </div>
</div>
@endsection

@section('js')

@endsection