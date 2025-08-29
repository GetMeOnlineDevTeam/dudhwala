 <div class="card mt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Host</th>
                            <th>Type</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Note</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($data as $item)
                        <tr>

                           <td>{{ $item->host ? $item->host->name : '-' }}</td>


                            <td>{{ $item->type }}</td>

                            <td class="support-message">
                                @if (strlen($item->message) > 50)
                                {{ \Illuminate\Support\Str::limit($item->message, 50) }}
                                <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#messageModal{{ $item->id }}">Read more</a>
                                <!-- Message Modal -->
                                <div class="modal fade" id="messageModal{{ $item->id }}" tabindex="-1" aria-labelledby="messageModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-md">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="messageModalLabel{{ $item->id }}">Full Message</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">{{ $item->message }}</div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                {{ $item->message }}
                                @endif
                            </td>

                            <td>
                                @if ($item->status == 'pending')
                                <span class="lable-table bg-warning-subtle text-warning rounded border border-warning-subtle font-text2 fw-bold">Pending</span>
                                @elseif ($item->status == 'approved')
                                <span class="lable-table bg-success-subtle text-success rounded border border-success-subtle font-text2 fw-bold">Approved</span>
                                @elseif ($item->status == 'rejected')
                                <span class="lable-table bg-danger-subtle text-danger rounded border border-danger-subtle font-text2 fw-bold">Rejected</span>
                                @endif
                            </td>

                            <td class="rejection-reason">
                                @if (!empty($item->rejection_reason) && strlen($item->rejection_reason) > 50)

                                {{ \Illuminate\Support\Str::limit($item->rejection_reason, 50) }}

                                <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#reasonModal{{ $item->id }}">Read more</a>

                                <!-- Rejection Reason Modal -->
                                <div class="modal fade" id="reasonModal{{ $item->id }}" tabindex="-1" aria-labelledby="reasonModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-md">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="reasonModalLabel{{ $item->id }}">Note</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">{{ $item->rejection_reason ?? 'No rejection reason provided.' }}</div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                {{ $item->rejection_reason ?? '--' }}
                                @endif
                            </td>

                            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>

                            <td>
                                <div class="dropdown">

                                    <!-- Action buttons -->
                                    <button class="btn btn-sm dropdown-toggle dropdown-toggle-nocaret"
                                        type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>

                                    <ul class="dropdown-menu">

                                        @if ($item->status !== 'approved')
                                        <li>
                                            <form method="POST" action="{{ route('admin.support.response.update', $item->id) }}" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-check-circle me-2"></i>Approve
                                                </button>
                                            </form>
                                        </li>
                                        @endif

                                        @if ($item->status !== 'rejected')
                                        <li>
                                            <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $item->id }}">
                                                <i class="bi bi-x-circle me-2"></i>Reject
                                            </button>
                                        </li>
                                        @endif

                                        <li>
                                            <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-md">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.support.response.update', $item->id) }}">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="rejectModalLabel{{ $item->id }}">Add a Note</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <textarea class="form-control" name="rejection_reason" id="rejection_reason{{ $item->id }}" rows="3" required></textarea>
                                                    </div>
                                                    <input type="hidden" name="status" value="rejected">
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-md">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.support.response.delete', $item->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $item->id }}">Delete Support Request</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete this support request?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-search fs-3 d-block mb-2"></i>
                                No support requests found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-4">
                    @include('components.pagination', ['paginator' => $data])
                </div>
            </div>
        </div>
    </div>