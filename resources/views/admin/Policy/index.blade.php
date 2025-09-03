@extends('shared.layouts.app')

@section('css')
<style>
    .policy-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(64, 81, 137, 0.07), 0 1.5px 4px rgba(60, 72, 100, 0.05);
        padding: 20px;
    }

    .badge-policy {
        background: #eef7f1;
        color: #116631;
        border: 1px solid #cfe9db;
        font-size: .8rem;
        padding: 4px 8px;
        border-radius: 999px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-control {
        min-height: 200px;
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
</style>
@endsection

@section('title', 'Policy')

@section('content')
<div class="main-content pt-0">
    <br>


    {{-- Breadcrumb --}}
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">
            <a href="{{ route('admin.policy.index') }}" style="color: inherit; text-decoration: none;">
                Policy Management
            </a>
        </div>
        <div class="ps-2"></div>
    </div>
    <!--end breadcrumb-->

    {{-- Policy Cards --}}
    <div class="row g-3">
        @foreach($policies as $policy)
        <div class="col-12 col-md-4">
            <div class="policy-card">
                <h5>{{ $policy->title }}</h5>
                <div class="badge-policy mb-3">{{ ucfirst($policy->type) }}</div>
                <p> {{ Str::limit($policy->text, 100) }}</p>

                <div class="d-flex justify-content-between">
@can('policy.edit')
                    <a href="{{ route('admin.policy.edit', $policy->id) }}" class="action-btn edit" title="Edit">
                        <span class="material-icons-outlined">edit</span>
                    </a>
                    @endcan

                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection

@section('js')

@endsection