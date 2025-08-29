<div class="table-responsive white-space-nowrap">
    <table class="table align-middle">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Registered at</th>
                <th>Account Status</th>

                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
            <tr>

                <td>
                    <a class="d-flex align-items-center gap-3" href="javascript:;">
                        <p class="mb-0 customer-name fw-bold">{{ $item->name ?? 'No Name' }}</p>
                    </a>
                </td>
                <td>
                    {{ $item->phone }}
                </td>
                <td>

                    <a href="javascript:;" class="font-text1">{{ $item->email }}</a>
                </td>

                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                <td>
                    @if ($item->is_suspended)
                    <span class="lable-table bg-danger-subtle text-danger rounded border border-danger-subtle font-text2 fw-bold">
                        Suspended <i class="bi bi-slash-circle ms-2"></i>
                    </span>
                    @else
                    <span class="lable-table bg-success-subtle text-success rounded border border-success-subtle font-text2 fw-bold">
                        Active <i class="bi bi-check-circle ms-2"></i>
                    </span>
                    @endif
                </td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-sm dropdown-toggle dropdown-toggle-nocaret"
                            type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item"
                                    href="{{ route('admin.view-attendee', ['id' => $item->id]) }}"><i
                                        class="bi bi-eye-fill me-2"></i>View</a>
                            </li>
                            <li><a class="dropdown-item"
                                    href="{{ route('admin.edit-attendee', ['id' => $item->id]) }}"><i
                                        class="bi bi-pencil-square me-2"></i>Edit</a></li>
                            <li class="dropdown-divider"></li>
                            <form id="deleteform{{ $item->id }}" method="POST"
                                action="{{ route('admin.suspend-attendee', ['id' => $item->id]) }}">
                                @csrf
                                @method('POST')
                                @if ($item->is_suspended)
                                <button type="button" class="dropdown-item text-success"
                                    onclick="suspend_account({{ $item->id }}, false)">
                                    <i class="bi bi-check-circle me-2"></i>Activate Account
                                </button>
                                @else
                                <button type="button" class="dropdown-item text-danger"
                                    onclick="suspend_account({{ $item->id }}, true)">
                                    <i class="bi bi-slash-circle me-2"></i>Suspend Account
                                </button>
                                @endif
                            </form>

                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i class="bi bi-search fs-3 d-block mb-2"></i>
                    No attendees found matching your criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @include('components.pagination', ['paginator' => $data])
</div>