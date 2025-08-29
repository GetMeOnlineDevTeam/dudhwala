@extends('shared.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
<style>
    .card {
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(64, 81, 137, .07), 0 1.5px 4px rgba(60, 72, 100, .05);
    }

    .img-holder {
        width: 240px;
        height: 160px;
        border: 1px dashed #cfd4dc;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: #f8fafc;
        overflow: hidden;
    }

    .img-holder img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .help {
        color: #6b7280;
        font-size: .9rem;
    }
</style>
@endsection

@section('title', 'Edit Community Moment')

@section('content')
<div class="main-content pt-0">
    <div class="container py-3">

        {{-- Breadcrumb --}}
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">
                <a href="{{ route('admin.community-moments') }}" style="color:inherit;text-decoration:none;">Community Moments</a>
            </div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Edit Community Moment</h4>
            <a href="{{ route('admin.community-moments') }}" class="btn btn-outline-secondary">← Back to list</a>
        </div>

        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>There were some problems with your submission:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.community-moments.update', $moment) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-5">
                            <label class="form-label">Photo</label>
                            <div class="img-holder mb-2">
                                <img id="previewImg"
                                    src="{{ $moment->image ? asset('storage/'.$moment->image) : 'https://placehold.co/480x320?text=Preview' }}"
                                    alt="Preview">
                            </div>

                            <input type="file"
                                class="form-control @error('image') is-invalid @enderror"
                                name="image" id="image"
                                accept="image/png,image/jpeg,image/jpg,image/webp"
                                onchange="previewFile(this)">
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-7">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description"
                                rows="6"
                                class="form-control @error('description') is-invalid @enderror"
                                maxlength="255"
                                placeholder="Write a short description...">{{ old('description', $moment->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Update Moment</button>

                        {{-- Optional inline delete --}}
                        <form method="POST"
                            action="{{ route('admin.community-moments.destroy', $moment) }}"
                            onsubmit="return confirm('Delete this moment?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Delete</button>
                        </form>

                        <a href="{{ route('admin.community-moments') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('js')
<script>
    function previewFile(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = e => document.getElementById('previewImg').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
</script>
@endsection