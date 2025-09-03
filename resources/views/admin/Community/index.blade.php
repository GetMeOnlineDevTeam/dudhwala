@extends('shared.layouts.app')

@section('title','Community Members')

@section('content')
<div class="main-content pt-0">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Community Members</h4>
            @can('community_members.create')
            <a href="{{ route('admin.community-members.create') }}" class="btn btn-primary">Add Member</a>
            @endcan
        </div>


        <form method="GET" action="{{ route('admin.community-members') }}" class="d-flex gap-2 mb-3">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name/designation…" style="max-width:260px;">
            @if(request('search'))
            <a href="{{ route('admin.community-members') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Priority</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $m)
                    <tr>
                        <td>{{ $m->id }}</td>
                        <td>
                            @if($m->image)
                            <img src="{{ asset('storage/'.$m->image) }}" alt="" class="rounded" style="width:56px;height:56px;object-fit:cover;">
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $m->name }}</td>
                        <td>{{ $m->designation ?? '—' }}</td>
                        
                        <td>
                            @can('community_members.priority')
                            <form method="POST"
                                action="{{ route('admin.community-members.priority', $m->id) }}"
                                class="priority-form d-inline-flex align-items-center gap-2">
                                @csrf
                                <input type="number"
                                    name="priority"
                                    class="form-control form-control-sm priority-input"
                                    value="{{ $m->priority }}"
                                    min="1"
                                    style="width:72px"
                                    max="{{ count($members) }}" />
                                <button type="submit" class="btn btn-sm btn-success update-btn" style="display:none;">
                                    Update
                                </button>
                            </form>
                            @endcan
                        </td>

                        <td>
                            @can('community_members.edit')
                            <a href="{{ route('admin.community-members.edit', $m->id) }}" title="Edit">
                                <span class="material-icons-outlined">edit</span>
                            </a>
                            @endcan
                            @can('community_members.delete')
                            <form action="{{ route('admin.community-members.destroy', $m->id) }}"
                                method="POST"
                                style="display:inline;"
                                onsubmit="return confirm('Are you sure you want to delete this member?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;border:none;padding:0;cursor:pointer;">
                                    <span class="material-icons-outlined" style="color:red;">delete</span>
                                </button>
                            </form>
                            @endcan
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No members found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('components.pagination', ['paginator' => $members])
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.priority-form').forEach(form => {
        const input = form.querySelector('.priority-input');
        const btn = form.querySelector('.update-btn');
        const orig = input.value;

        input.addEventListener('input', () => {
            btn.style.display = (input.value !== orig && input.value !== '') ? 'inline-block' : 'none';
        });
    });
</script>
@endsection