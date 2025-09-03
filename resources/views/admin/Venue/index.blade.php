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

@section('title', 'Venues')

@section('content')
<div class="main-content pt-0">
    <br>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">
            <a href="{{ route('venues') }}" style="color: inherit; text-decoration: none;">
                Venues
            </a>
        </div>
    </div>
    <!--end breadcrumb-->

    @php
    $hasFilters = request()->filled('search') || request()->filled('date_filter');
    @endphp

    <div class="row g-3">
        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.venues') }}" class="d-flex flex-wrap gap-2 mb-4 w-100">
            <div class="custom-input-icon">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    style="width:200px"
                    placeholder="Search venues…">
            </div>

            @if(request('search'))
            <a href="{{ route('admin.venues') }}" class="btn btn-outline-secondary">
                Clear
            </a>
            @endif
            @can('venues.create')
            <a href="{{ route('admin.venues.create') }}" class="btn btn-outline-secondary ms-auto">Create Venue</a>
            @endcan
        </form>
    </div>

    <div class="table-responsive">
        <br>
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>City</th>

                    <th>Cover Image</th>
                    <th>Actions</th>
                </tr>
            </thead> 
            <tbody>
                @forelse($venues as $venue)
                <tr>
                    <td>{{ $venue->id }}</td>
                    <td>{{ $venue->name }}</td>
                    <td>{{ optional($venue->address)->city ?? '-' }}</td>

                    <td>
                        @foreach($venue->images as $image)
                        @if($image->is_cover)
                        <img
                            src="{{ asset('storage/'.$image->image) }}"
                            alt="Cover Image"
                            class="img-thumbnail"
                            style="width: 150px; height: 150px; object-fit: cover;">
                        @endif
                        @endforeach
                    </td>
                    <td>
                        @can('venues.edit')
                        <a href="{{ route('admin.venues.edit', $venue->id) }}" title="Edit">
                            <span class="material-icons-outlined">edit</span>
                        </a>
                        @endcan
                        @can('venues.delete')
                        <form action="{{ route('admin.venues.destroy', $venue->id) }}" method="POST" style="display:inline;"
                            onsubmit="return confirm('Are you sure you want to delete this venue?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none;border:none;padding:0;cursor:pointer;">
                                <span style="color: red;" class="material-icons-outlined">delete</span>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        No venues found{{ $hasFilters ? ' for current filter' : '' }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @include('components.pagination', ['paginator' => $venues])
    </div>
</div>
@endsection

@section('js')

@endsection