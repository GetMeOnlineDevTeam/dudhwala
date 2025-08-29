@extends('shared.layouts.app')

@section('title', 'Edit Policy')

@section('css')
    <style>
        .ck-editor__editable_inline {
            min-height: 250px;
            max-height: 250px;
            overflow-y: auto;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <!--breadcrumb-->
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="{{ route('admin.dashboard') }}" class="text-dark text-decoration-none fs-6 fw-semibold">Dashboard</a>
            <span class="fs-5 text-muted">›</span>
            <span class="fs-6 fw-semibold text-teal">Policy Management</span>
            <span class="fs-6 text-muted">›</span>
            <span class="fs-6 fw-semibold text-teal">Edit Policy</span>
        </div>
        <!--end breadcrumb-->

        <div class="row">
            <div class="col-12 mx-auto">
                <form action="{{ route('admin.policy.update', $policy->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card mb-4">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Edit {{ ucfirst($policy->type) }} Policy</h5>

                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input
                                    type="text"
                                    name="title"
                                    id="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $policy->title) }}"
                                    required
                                >
                                @error('title')
                                    <div class="text-danger mt-1 small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="text" class="form-label">Content</label>
                                <textarea
                                    name="text"
                                    id="textEditor"
                                    class="form-control @error('text') is-invalid @enderror"
                                    rows="10"
                                >{{ old('text', $policy->text) }}</textarea>
                                @error('text')
                                    <div class="text-danger mt-1 small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.policy.index') }}" class="btn btn-light px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor.create(document.querySelector('#textEditor')).catch(err => console.error(err));
    </script>
@endsection
