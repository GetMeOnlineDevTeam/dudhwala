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

    .vr-label {
        font-size: .8rem;
        color: #6c757d
    }
</style>
@endsection

@section('title', 'Contact Requests')

@section('content')
<div class="main-content pt-0">
    <br>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">
            <a href="{{ route('admin.contact-requests') }}" style="color: inherit; text-decoration: none;">
                Contact Requests
            </a>
        </div>
        <div class="ps-2"></div>
    </div>
    <!--end breadcrumb-->

    @php
    $hasFilters = request()->filled('q') || request()->filled('from') || request()->filled('to');
    @endphp

    {{-- Filters --}}
    <div class="row g-3">
        <form method="GET" action="{{ route('admin.contact-requests') }}" class="d-flex flex-wrap gap-2 mb-4">
            <div class="position-relative custom-input-icon">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control ps-4" style="width:230px" placeholder="Search…">
            </div>

            <input type="date" name="from" value="{{ request('from') }}" class="form-control" style="width:170px">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control" style="width:170px">


            <button type="submit" class="btn btn-primary">Apply</button>

            @if($hasFilters)
            <a href="{{ route('admin.contact-requests') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <br>
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th style="min-width:140px">Received</th>
                    <th style="min-width:130px">Name</th>
                    <th style="min-width:130px">Phone</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th class="text-end" style="min-width:120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $row)
                @php
                $payload = [
                'id' => $row->id,
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'phone_no' => $row->phone_no,
                'subject' => $row->subject,
                'message' => $row->message,
                'created_at' => $row->created_at?->format('d M Y, H:i'),
                'updated_at' => $row->updated_at?->format('d M Y, H:i'),
                ];
                @endphp
                <tr>
                    <td class="text-muted">
                        {{ $row->created_at->format('d M Y, H:i') }}
                    </td>
                    <td>
                        @if($row->first_name || $row->last_name)
                        {{ trim($row->first_name . ' ' . $row->last_name) }}
                        @else
                        <span class="text-muted">Anonymous</span>
                        @endif
                    </td>
                    <td>{{ $row->phone_no }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($row->subject, 40) }}</td>
                    <td class="text-muted">{{ \Illuminate\Support\Str::limit($row->message, 70) }}</td>
                    <td class="text-end">
                        <div class="d-inline-flex align-items-center gap-2">
                            {{-- View in modal --}}
                            @can('contact_requests.view')
                            <button type="button"
                                class="btn btn-outline-secondary btn-sm btn-view-request"
                                data-request='@json($payload)'
                                data-bs-toggle="modal"
                                data-bs-target="#viewRequestModal"
                                title="View">
                                <span class="material-icons-outlined" style="vertical-align:middle">visibility</span>
                            </button>
                            @endcan
                            @can('contact_requests.delete')

                            {{-- Delete --}}
                            <form action="{{ route('admin.contact-requests.destroy', $row) }}" method="POST"
                                onsubmit="return confirm('Delete this request? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                    <span class="material-icons-outlined" style="vertical-align:middle">delete</span>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        No contact requests found{{ $hasFilters ? ' for current filter' : '' }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @include('components.pagination', ['paginator' => $requests])
    </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="viewRequestModal" tabindex="-1" aria-labelledby="viewRequestLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="viewRequestLabel">Contact Request</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="vr-label">Received</div>
                        <div id="vrCreated" class="fw-semibold">—</div>
                    </div>
                    <div class="col-md-6">
                        <div class="vr-label">Updated</div>
                        <div id="vrUpdated" class="fw-semibold">—</div>
                    </div>

                    <div class="col-md-6">
                        <div class="vr-label">Name</div>
                        <div id="vrName" class="fw-semibold">—</div>
                    </div>

                    <div class="col-md-6">
                        <div class="vr-label">Phone</div>
                        <div class="fw-semibold">
                            <a id="vrPhone" href="#" target="_blank" rel="noopener">—</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="vr-label">ID</div>
                        <div id="vrId" class="fw-semibold">—</div>
                    </div>
                </div>

                <hr class="my-4" />

                <div class="mb-3">
                    <div class="vr-label">Subject</div>
                    <div id="vrSubject" class="fw-semibold">—</div>
                </div>

                <div>
                    <div class="vr-label mb-1">Message</div>
                    <div id="vrMessage" class="text-muted" style="white-space: pre-wrap;">—</div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="d-flex gap-2">

                </div>
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('viewRequestModal');
        const created = modal.querySelector('#vrCreated');
        const updated = modal.querySelector('#vrUpdated');
        const nameEl = modal.querySelector('#vrName');
        const idEl = modal.querySelector('#vrId');
        const subj = modal.querySelector('#vrSubject');
        const msg = modal.querySelector('#vrMessage');
        const phoneA = modal.querySelector('#vrPhone');
        const callBtn = modal.querySelector('#vrCallBtn');
        const smsBtn = modal.querySelector('#vrSmsBtn');

        document.querySelectorAll('.btn-view-request').forEach(btn => {
            btn.addEventListener('click', () => {
                const data = JSON.parse(btn.getAttribute('data-request') || '{}');

                // Fill
                created.textContent = data.created_at || '—';
                updated.textContent = data.updated_at || '—';
                const firstName = (data.first_name || '').trim();
                const lastName = (data.last_name || '').trim();
                nameEl.textContent = (firstName || lastName) ?
                    `${firstName} ${lastName}`.trim() :
                    '—';
                idEl.textContent = data.id ?? '—';
                subj.textContent = data.subject || '—';
                msg.textContent = data.message || '—';

                // Phone links
                const phone = (data.phone_no || '').toString().trim();
                if (phone) {
                    phoneA.textContent = phone;
                    phoneA.href = 'tel:' + phone;
                    callBtn.href = 'tel:' + phone;
                    smsBtn.href = 'sms:' + phone;
                } else {
                    phoneA.textContent = '—';
                    phoneA.removeAttribute('href');
                    callBtn.removeAttribute('href');
                    smsBtn.removeAttribute('href');
                }

                // Title with ID
                const title = modal.querySelector('#viewRequestLabel');
                title.textContent = 'Contact Request #' + (data.id ?? '—');
            });
        });
    });
</script>
@endsection