                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="overflow-x: auto; white-space: nowrap;">
                        <thead class="table-light">
                            <tr>
                                <th>Id</th>

                                @unless (authUser()->hasRole('host','host'))
                                <th>User</th>
                                @endunless

                                <th>Workshop</th>

                                <th>Status</th>
                                
                                <th>Amount</th>
                                
                                <th>Payment status</th>
                                
                                <th>Created At</th>
                                {{-- <th>Actions</th> --}}
                            </tr>
                        </thead>
                        <tbody>

                            @forelse ($data as $item)
                            <tr>
                                <td>
                                    <p class="mb-0 customer-name fw-bold">{{ $item->id }}</p>
                                </td>
                                @unless (authUser()->hasRole('host','host'))
                                <td>
                                    <p class="mb-0 customer-name fw-bold">{{ $item->user->name }}</p>
                                </td>
                                @endunless
                                <td>
                                    <p class="mb-0 customer-name">{{ $item->workshop->title }}</p>
                                </td>

                                <td>
                                    @if ($item->status === 'pending')
                                    <span
                                        class="lable-table bg-warning-subtle text-warning rounded border border-warning-subtle font-text2 fw-bold">Pending<i
                                            class="bi bi-info-circle ms-2"></i></span>
                                    @elseif ($item->status === 'approved')
                                    <span
                                        class="lable-table bg-success-subtle text-success rounded border border-success-subtle font-text2 fw-bold">Confirmed<i
                                            class="bi bi-check2 ms-2"></i></span>
                                    @elseif ($item->status === 'rejected')
                                    <span
                                        class="lable-table bg-danger-subtle text-danger rounded border border-danger-subtle font-text2 fw-bold">Cancelled<i
                                            class="bi bi-x-circle ms-2"></i></span>
                                    @endif
                                </td>
                                
                                <td>₹{{ number_format($item->amount, 2) }}</td>
                                
                                <td>
                                    @if ($item->payment_status === 'pending')
                                    <span
                                        class="lable-table bg-warning-subtle text-warning rounded border border-warning-subtle font-text2 fw-bold">Pending<i
                                            class="bi bi-info-circle ms-2"></i></span>
                                    @elseif ($item->payment_status === 'paid')
                                    <span
                                        class="lable-table bg-success-subtle text-success rounded border border-success-subtle font-text2 fw-bold">Paid<i
                                            class="bi bi-check2 ms-2"></i></span>
                                    @endif
                                </td>
                                
                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}</td>
                            </tr>
                            
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    <i class="bi bi-search fs-3 d-block mb-2"></i>
                                    No booking found .
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        @include('components.pagination', ['paginator' => $data])
                    </div>

                </div>