@extends('shared.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
<style>
  .card { border-radius:16px; box-shadow:0 2px 8px rgba(64,81,137,.07),0 1.5px 4px rgba(60,72,100,.05); }
  .thumb { width:150px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; }
  .search-wrap{ position:relative; }
  .search-wrap .material-icons-outlined{ position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; }
  .search-wrap input{ padding-left:36px; width:220px; }
</style>
@endsection

@section('title', 'Community Moments')

@section('content')
<div class="main-content pt-0">
  <div class="container py-3">

    {{-- Breadcrumb --}}
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">
        <a href="{{ route('admin.dashboard') }}" style="color:inherit; text-decoration:none;">Dashboard</a>
      </div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item active" aria-current="page">Community Moments</li>
          </ol>
        </nav>
      </div>
    </div>


    {{-- Filters / Actions --}}
    @php $hasFilters = request()->filled('search'); @endphp
    <form method="GET" action="{{ route('admin.community-moments') }}" class="d-flex flex-wrap align-items-center gap-2 mb-3">
      <div class="search-wrap">
        <span class="material-icons-outlined">search</span>
        <input type="search" name="search" class="form-control" placeholder="Search moments…"
               value="{{ request('search') }}">
      </div>

      @if($hasFilters)
        <a href="{{ route('admin.community.moments') }}" class="btn btn-outline-secondary">Clear</a>
      @endif
@can('community_moments.create')
      <a href="{{ route('admin.community-moments.create') }}" class="btn btn-primary ms-auto">
        <i class="bi bi-plus-lg me-1"></i> Add Moment
      </a>
      @endcan
    </form>

    {{-- Table --}}
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th style="width:120px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($moments as $m)
                <tr>
                  <td>{{ $m->id }}</td>
                  <td>
                    @if($m->image)
                      <img src="{{ asset('storage/'.$m->image) }}" class="thumb" alt="Moment">
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>{{ $m->description }}</td>
                  <td>
                    @can('community_moments.edit')
                    <a href="{{ route('admin.community-moments.edit', $m) }}" title="Edit">
                      <span class="material-icons-outlined">edit</span>
                    </a>
                    @endcan
                    @can('community_moments.delete')
                    <form action="{{ route('admin.community-moments.destroy', $m) }}"
                          method="POST" style="display:inline;"
                          onsubmit="return confirm('Delete this moment?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" style="background:none;border:none;padding:0;cursor:pointer;" title="Delete">
                        <span class="material-icons-outlined" style="color:#fe3f52;">delete</span>
                      </button>
                    </form>
                    @endcan
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">
                    <i class="bi bi-image fs-3 d-block mb-2"></i>
                    No moments found{{ $hasFilters ? ' for the current filter' : '' }}.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        @include('components.pagination', ['paginator' => $moments])
      </div>
    </div>

  </div>
</div>
@endsection

@section('js')
@endsection
