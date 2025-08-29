@extends('shared.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
@endsection

@section('title', 'Homepage Banner')

@section('content')
<div class="main-content pt-0">
    <br>

    <!-- breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">
            <a href="{{ route('admin.banner.edit') }}" style="color: inherit; text-decoration: none;">
                Homepage Banner
            </a>
        </div>
    </div>
    <!-- end breadcrumb -->

    <br>



    <!-- Edit banner form -->
    <div class="card">
        <div class="card-header">
            <h3>Edit Banner</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.banner.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    @if($banner && $banner->banner)
                    <div class="mt-3">
                        <!-- Display the current banner image -->
                        <img src="{{ asset('storage/' . $banner->banner) }}" alt="Current Banner" class="img-fluid" style="max-width: 300px;">
                    </div>
                    <br>
                    @endif
                    <input type="file" class="form-control" id="banner" name="banner" accept="image/*">
                    @error('banner')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update Banner</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')

@endsection