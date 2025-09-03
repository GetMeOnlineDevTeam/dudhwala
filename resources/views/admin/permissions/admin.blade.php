@extends('shared.layouts.app')
@section('title', 'Admin Permissions - Dudhwala')

@section('content')
<div class="main-content pt-0">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">
            <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">
                Dashboard
            </a>
        </div>
        <div class="ps-3">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-6 fw-semibold text-teal">Permissions</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <h5 class="mb-0 fw-bold">Admin Permissions</h5>
                <span class="badge bg-primary rounded-pill">Role: {{ ucfirst($roleName) }}</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="position-relative me-2">
                    <input id="perm-search" class="form-control px-5" type="search" placeholder="Search permissions">
                    <span class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50 fs-5">search</span>
                </div>
                <button type="button" id="btn-check-all" class="btn btn-outline-secondary">Check All</button>
                <button type="button" id="btn-uncheck-all" class="btn btn-outline-secondary">Uncheck All</button>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.permissions.admin.update') }}" class="mb-5">
        @csrf

        <div class="row g-3">
            @foreach($grouped as $module => $perms)
                <div class="col-12 col-md-6 col-xl-4 perm-card" data-module="{{ $module }}">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="material-icons-outlined">apps</span>
                                    <h6 class="mb-0 fw-bold text-capitalize">{{ str_replace('_',' ', $module) }}</h6>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-filter dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Quick
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item btn-module-check" href="javascript:;" data-module="{{ $module }}">Select All</a></li>
                                        <li><a class="dropdown-item btn-module-uncheck" href="javascript:;" data-module="{{ $module }}">Unselect All</a></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="list-group list-group-flush small">
                                @foreach($perms as $perm)
                                    @php $isChecked = in_array($perm->name, $assigned, true); @endphp
                                    <label class="list-group-item d-flex align-items-center justify-content-between perm-row"
                                           data-perm="{{ $perm->name }}">
                                        <span class="text-muted">{{ $perm->name }}</span>
                                        <input class="form-check-input ms-2 perm-checkbox"
                                               type="checkbox"
                                               name="permissions[]"
                                               value="{{ $perm->name }}"
                                               @checked($isChecked)>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex align-items-center justify-content-end mt-4">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check2-circle me-2"></i>Save Changes
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    // global check/uncheck
    document.getElementById('btn-check-all')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('btn-uncheck-all')?.addEventListener('click', function() {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    });

    // per-module quick actions
    document.querySelectorAll('.btn-module-check').forEach(btn => {
        btn.addEventListener('click', function() {
            const module = this.dataset.module;
            document.querySelectorAll(`.perm-card[data-module="${module}"] .perm-checkbox`).forEach(cb => cb.checked = true);
        });
    });
    document.querySelectorAll('.btn-module-uncheck').forEach(btn => {
        btn.addEventListener('click', function() {
            const module = this.dataset.module;
            document.querySelectorAll(`.perm-card[data-module="${module}"] .perm-checkbox`).forEach(cb => cb.checked = false);
        });
    });

    // search filter
    const search = document.getElementById('perm-search');
    if (search) {
        search.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            document.querySelectorAll('.perm-card').forEach(card => {
                let hit = false;
                const moduleName = (card.dataset.module || '').toLowerCase();
                if (moduleName.includes(term)) hit = true;
                card.querySelectorAll('.perm-row').forEach(row => {
                    const name = (row.dataset.perm || '').toLowerCase();
                    if (name.includes(term)) hit = true;
                });
                card.style.display = hit ? '' : 'none';
            });
        });
    }
})();
</script>
@endpush
@endsection
