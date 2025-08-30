@extends('shared.layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
    <style>
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
            padding: 24px;
            margin-top: 20px;
            background: #fff;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .submit-btn {
            background-color: #16a34a;
            border: none;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            border-radius: 10px;
            width: 100%;
            margin-top: 25px;
            transition: background-color .2s ease-in-out;
        }

        .submit-btn:hover {
            background-color: #15803d;
        }
    </style>
@endsection

@section('title', 'Edit Configuration Settings')

@section('content')
    <div class="main-content pt-0">
        <div class="container py-3">

            {{-- Breadcrumb --}}
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">
                    <a href="{{ route('admin.configurations.index') }}"
                        style="color: inherit; text-decoration: none;">Configuration</a>
                </div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item active" aria-current="page">Edit Configuration</li>
                        </ol>
                    </nav>
                </div>
            </div>

            {{-- Page header --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-0">Edit Configuration</h4>
                <a href="{{ route('admin.configurations.index') }}" class="btn btn-outline-secondary">
                    ← Back to list
                </a>
            </div>

            {{-- Form card --}}
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

                    <form action="{{ route('admin.configurations.update') }}" method="POST">
    @csrf
    @method('PUT')

                        @foreach ($configurations as $index => $configuration)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Key <span class="text-danger">*</span></label>
                                    <input type="text" name="configurations[{{ $index }}][key]"
                                        class="form-control @error('configurations.' . $index . '.key') is-invalid @enderror"
                                        value="{{ old('configurations.' . $index . '.key', $configuration->key) }}"
                                        maxlength="255" required readonly>
                                    @error('configurations.' . $index . '.key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Value <span class="text-danger">*</span></label>
                                    <input type="number" name="configurations[{{ $index }}][value]"
                                        class="form-control @error('configurations.' . $index . '.value') is-invalid @enderror"
                                        value="{{ old('configurations.' . $index . '.value', $configuration->value) }}"
                                        required>
                                    @error('configurations.' . $index . '.value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Hidden ID field so we know which record to update --}}
                                <input type="hidden" name="configurations[{{ $index }}][id]"
                                    value="{{ $configuration->id }}">
                            </div>
                        @endforeach

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">Update Configurations</button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection
