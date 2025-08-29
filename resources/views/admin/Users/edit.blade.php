@extends('shared.layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/components/image-input.css') }}">
<style>
    .status-tabs .nav-link {
        /* ... your existing styles ... */
    }
    .custom-input-icon { /* ... */ }

    /* Overlay for inline popup */
    #docOverlay {
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0,0,0,0.6);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1050;
    }
    #docOverlay img {
      max-width: 90%;
      max-height: 90%;
      object-fit: contain;
      box-shadow: 0 4px 20px rgba(0,0,0,0.5);
      border-radius: 4px;
    }
    #docOverlay .close-btn {
      position: absolute;
      top: 20px; right: 20px;
      background: rgba(255,255,255,0.8);
      border: none;
      font-size: 1.5rem;
      width: 36px; height: 36px;
      border-radius: 50%;
      cursor: pointer;
    }
</style>
@endsection

@section('title', 'Users')

@section('content')
<div class="main-content pt-0">
    <br>
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">
            <a href="{{ route('admin.users') }}" style="color: inherit; text-decoration: none;">
                Users
            </a>
        </div>
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="#"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">User Profile</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <div class="card mb-4">
        <div class="card-body">
            {{-- Combined Name field --}}
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label">Name</label>
                    <input
                        type="text"
                        class="form-control"
                        value="{{ $user->first_name }} {{ $user->last_name }}"
                        disabled>
                </div>
            </div>

            {{-- Verification / Documents --}}
            @if($user->is_verified)
                <div class="mb-4">
                    <span class="badge bg-success fs-5">Verified User</span>
                </div>
            @else
                <div class="mb-4">
                    <h5 class="mb-2">Uploaded Documents</h5>

                    @if($user->documents->isEmpty())
                        <p class="text-muted">No documents uploaded.</p>
                    @else
                        <ul class="list-group mb-3">
                            @foreach($user->documents as $doc)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ ucfirst($doc->document_type) }}
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        onclick="showDocument('{{ Storage::url($doc->document) }}')">
                                        View
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    {{-- Accept / Reject --}}
                    <form action="{{ route('admin.users.verify', $user) }}" method="POST" class="d-flex gap-2">
                        @csrf
                        <button type="submit" name="action" value="accept" class="btn btn-success">
                            Accept
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                            Reject
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- User’s Bookings --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Recent Bookings</h3>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Venue</th>
                    <th>Time-slot</th>
                    <th>Booking Date</th>
                    <th>Payment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td>{{ $booking->id }}</td>
                        <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                        <td>{{ $booking->venue->name }}</td>
                        <td>
                            {{ $booking->timeSlot->name }}
                            @if($booking->full_time)
                                <br>
                                <span class="badge bg-info" style="font-size: small;">Full Day</span>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</td>
                        <td>₹{{ number_format($booking->payment->amount) }}</td>
                        <td>
                            @if($booking->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($booking->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @else
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No bookings found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- pagination --}}
        @include('components.pagination', ['paginator' => $bookings])
    </div>
</div>

{{-- Inline document overlay --}}
<div id="docOverlay" onclick="hideDocument()">
  <button class="close-btn">&times;</button>
  <img id="docImage" src="" alt="Document">
</div>
@endsection

@section('js')

<script>
  function showDocument(url) {
    const overlay = document.getElementById('docOverlay');
    const img = document.getElementById('docImage');
    img.src = url;
    overlay.style.display = 'flex';
  }
  function hideDocument() {
    const overlay = document.getElementById('docOverlay');
    overlay.style.display = 'none';
    document.getElementById('docImage').src = '';
  }
  document.getElementById('docImage').addEventListener('click', e => e.stopPropagation());
  document.querySelector('#docOverlay .close-btn').addEventListener('click', hideDocument);
</script>
@endsection
