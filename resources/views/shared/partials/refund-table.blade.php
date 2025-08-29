 <div class="card mt-4">
     <div class="card-body">
         <div class="product-table">
             <div class="table-responsive white-space-nowrap">
                 <table class="table align-middle">
                     <thead class="table-light">
                         <tr>
                             <th>Refund Id</th>
                             <th>Booking Id</th>
                             <th>User</th>
                             <th>Seats</th>
                             <th>Amount</th>
                             <th>Requested at</th>
                             <th>Status</th>
                             <th>Action</th>
                         </tr>
                     </thead>
                     <tbody>
                         @forelse($data as $item)
                         <tr>
                             <td>{{ $item['refund_id'] }}</td>
                             <td>{{ $item['booking_id'] }}</td>
                             <td>{{ $item['user_name'] }}</td>
                             <td>{{ $item['seats'] }}</td>
                             <td>₹{{ $item['amount'] }}</td>
                             <td>{{ $item['requested_at'] }}</td>
                             <td>
                                 @if ($item['status'] == 'initiated')
                                 <span class="lable-table bg-warning-subtle text-warning rounded border border-warning-subtle font-text2 fw-bold">
                                     Initiated <i class="bi bi-info-circle ms-2"></i>
                                 </span>
                                 @elseif($item['status'] == 'processing')
                                 <span class="lable-table bg-primary-subtle text-primary rounded border border-primary-subtle font-text2 fw-bold">
                                     Processing <i class="bi bi-arrow-repeat ms-2"></i>
                                 </span>
                                 @elseif($item['status'] == 'refunded')
                                 <span class="lable-table bg-success-subtle text-success rounded border border-success-subtle font-text2 fw-bold">
                                     Refunded <i class="bi bi-check-circle ms-2"></i>
                                 </span>
                                 @elseif($item['status'] == 'rejected')
                                 <span class="lable-table bg-danger-subtle text-danger rounded border border-danger-subtle font-text2 fw-bold">
                                     Rejected <i class="bi bi-x-circle ms-2"></i>
                                 </span>
                                 @elseif($item['status'] == 'failed')
                                 <span class="lable-table bg-danger-subtle text-danger rounded border border-danger-subtle font-text2 fw-bold">
                                     Failed <i class="bi bi-exclamation-triangle ms-2"></i>
                                 </span>
                                 @else
                                 <span class="lable-table bg-secondary-subtle text-secondary rounded border border-secondary-subtle font-text2 fw-bold">
                                     Unknown <i class="bi bi-question-circle ms-2"></i>
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
                                         <li>
                                             <form action="{{ route('admin.approve.refund',['id'=>$item['refund_id']]) }}" method="POST" class="d-inline">
                                                 @csrf
                                                 <input type="hidden" name="refund_id" value="{{ $item['refund_id'] }}">
                                                 <button type="submit" class="dropdown-item">
                                                     <i class="bi bi-check-circle me-2"></i>Approve Refund
                                                 </button>
                                             </form>
                                         </li>
                                         <li>
                                             <form action="{{ route('admin.reject.refund', ['id'=>$item['refund_id']]) }}" method="POST" class="d-inline">
                                                 @csrf
                                                 <input type="hidden" name="refund_id" value="{{ $item['refund_id'] }}">
                                                 <button type="submit" class="dropdown-item text-danger">
                                                     <i class="bi bi-x-circle me-2"></i>Reject Refund
                                                 </button>
                                             </form>
                                         </li>
                                     </ul>
                                 </div>
                             </td>
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
             </div>
         </div>
     </div>
 </div>

 {{-- Pagination --}}
 @include('components.pagination', ['paginator' => $data])