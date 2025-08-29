<div class="table-responsive white-space-nowrap">
    <table class="table align-middle">
        <thead class="table-light">
            <tr>
                @if(!Route::is('admin.view-attendee'))
                <th>User Name</th>
                @endif

                @if (!Route::is('admin.workshops.details') && !Route::is('host.workshops.details') )
                <th>Workshop</th>
                @endif

                <th>Rating</th>
                <th>Review</th>
                <th style="width: 120px;">Status</th>
                <th style="">Scout</th>
                <th style="width: 140px;">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
            <tr>
                @if(!Route::is('admin.view-attendee'))
                <td>
                    {{ $item->user->name ?? 'N/A' }}
                </td>
                @endif

                @if (!Route::is('admin.workshops.details') && !Route::is('host.workshops.details') )
                <td>
                    {{ $item->workshop->title ?? 'N/A' }}
                </td>
                @endif


                <td>
                    <div class="product-rating text-warning">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($item->rating >= $i)
                            <i class="bi bi-star-fill"></i>
                            @elseif($item->rating > $i - 1)
                            <i class="bi bi-star-half"></i>
                            @else
                            <i class="bi bi-star"></i>
                            @endif
                            @endfor
                    </div>
                </td>
                <td class="review-desc">
                    <strong>{{ $item->title }}</strong><br>
                    @if (strlen($item->body) > 80)
                    {{ \Illuminate\Support\Str::limit($item->body, 80) }}
                    <a href="javascript:;" data-bs-toggle="modal"
                        data-bs-target="#reviewModal{{ $item->id }}">
                        Read more
                    </a>
                    <!-- Description Modal -->
                    <div class="modal fade" id="reviewModal{{ $item->id }}" tabindex="-1"
                        aria-labelledby="reviewModalLabel{{ $item->id }}"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-md">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"
                                        id="reviewModalLabel{{ $item->id }}">
                                        {{ $item->title }}
                                    </h5>
                                    <button type="button" class="btn-close"
                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    {{ $item->body }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    {{ $item->body }}
                    @endif
                </td>

                <td>
                    {{-- @if ($item->status == 'pending') --}}
                    <span
                        class="lable-table bg-warning-subtle text-warning rounded border border-warning-subtle font-text2 fw-bold">Pending<i
                            class="bi bi-info-circle ms-2"></i></span>
                    {{-- @elseif($item->status == 'approved')
                                              <span
                                                  class="lable-table bg-success-subtle text-success rounded border border-success-subtle font-text2 fw-bold">Approved<i
                                                      class="bi bi-check2 ms-2"></i></span>
                                          @endif --}}
                </td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input scout-toggle" type="checkbox"
                            data-id="{{ $item->id }}" {{ $item->is_scout ? 'checked' : '' }}>
                    </div>
                </td>

                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('M d, h:i A') }}</td>
            </tr>

            @empty
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="bi bi-search fs-3 d-block mb-2"></i>
                    No data found matching your criteria.
                </td>
            </tr>
            @endforelse

        </tbody>
    </table>

    @include('components.pagination', ['paginator' => $data])
</div>



@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Toggle Scout --}}
<script>
    $('.scout-toggle').on('change', function() {
        let id = $(this).data('id');

        $.post(`/admin/toggle-scout/${id}`, {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    text: response.scout ? 'Marked as Scout Story' : 'Unmarked as Scout Story',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });
</script>
@endsection