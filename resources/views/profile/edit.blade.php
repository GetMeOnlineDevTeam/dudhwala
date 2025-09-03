<x-app-layout>
    @include('shared.layouts.app')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root{
            --brand:#116631; --brand-600:#0e4f27; --muted:#6b7280; --line:#e5e7eb;
            --ink:#111827; --ink-2:#374151; --bg:#fafafa; --card:#ffffff;
        }

        /* ---------- PAGE-SCOPED LAYOUT FIXES ---------- */
        /* Add .profile-page to <body> (script below). These overrides only affect this page. */
        body.profile-page main.main-wrapper{
            margin-top: 0 !important;
            margin-left: 0 !important;
            padding-top: 0 !important;
            background: transparent !important;
        }
        /* Hide hero/banner layers if your layout injects them */
        body.profile-page .banner,
        body.profile-page .banner::before,
        body.profile-page .banner::after{
            display:none !important;
        }

        /* ---------- PAGE UI ---------- */
        .section-wrap{max-width:1100px;margin:24px auto;padding:0 12px;}
        .card{background:var(--card);border:1px solid var(--line);border-radius:14px;box-shadow:0 2px 10px rgba(16,24,40,.04);}
        .card + .card{margin-top:24px;}
        .card-head{padding:18px 20px;border-bottom:1px solid var(--line);font-weight:700;color:var(--ink);}
        .card-body{padding:18px 20px;}

        .table-wrap{overflow:auto;}
        .table{width:100%;border-collapse:separate;border-spacing:0;}
        .table th,.table td{padding:12px 14px;white-space:nowrap;border-bottom:1px solid var(--line);font-size:14px;}
        .table thead th{position:sticky;top:0;background:var(--bg);font-weight:600;color:var(--ink-2);z-index:1;}
        .table tbody tr:nth-child(odd){background:#fbfbfb;}
        .table tbody tr:hover{background:#f7faf9;}
        .table th:first-child,.table td:first-child{padding-left:18px;}
        .table th:last-child,.table td:last-child{padding-right:18px;}

        .badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;line-height:1;}
        .badge-success{color:#065f46;background:#d1fae5;}
        .badge-warn{color:#92400e;background:#fef3c7;}
        .badge-danger{color:#991b1b;background:#fee2e2;}

        .btn-pill{padding:6px 12px;border:2px solid var(--brand);color:var(--brand);border-radius:999px;background:#fff;font-weight:700;display:inline-block}
        .btn-pill:hover{background:rgba(17,102,49,.06);}

        .form-label{font-weight:700;color:var(--ink);display:block;margin-bottom:6px;}
        .form-control{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-size:15px;outline:none;}
        .form-control:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(17,102,49,.12);}
        .help{color:var(--muted);font-size:12px;margin-top:6px;}

        .mb-2{margin-bottom:8px;} .mb-3{margin-bottom:14px;} .mb-4{margin-bottom:18px;} .mt-4{margin-top:18px;}
        .w-100{width:100%;}

        .input-group{display:flex;align-items:stretch;}
        .input-group input{flex:1 1 auto;border-radius:8px 0 0 8px;}
        .send-btn{border:1px solid var(--brand);color:var(--brand);background:#fff;padding:0 14px;border-radius:0 8px 8px 0;font-weight:700;}
        .send-btn:disabled{opacity:.6;cursor:not-allowed;}

        .otp-boxes{display:flex;gap:10px;}
        .otp-box{width:52px;height:48px;text-align:center;font-size:20px;border:1px solid var(--line);border-radius:8px;}
        .otp-box:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(17,102,49,.12);}

        .action-primary{background:var(--brand);color:#fff;border:none;border-radius:10px;padding:12px;font-weight:800;}
        .action-primary:hover{background:var(--brand-600);}
        .alert{padding:10px 12px;border-radius:8px;border:1px solid #fecaca;background:#fee2e2;color:#7f1d1d;font-size:14px;}
        .status-alert{padding:10px 12px;border-radius:8px;border:1px solid #bbf7d0;background:#dcfce7;color:#14532d;font-size:14px;}

        @media (max-width: 768px){
            .card-body{padding:14px;}
            .table th,.table td{padding:10px 12px;}
        }
    </style>

    <div class="section-wrap" x-data="profilePage()">
    {{-- BOOKINGS CARD --}}
    <div class="card">
        <div class="card-head">My Bookings</div>
        <div class="card-body table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Venue</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Status</th>
                    <th>Community</th>   {{-- NEW --}}
                    <th>Discount</th>    {{-- NEW --}}
                    <th>Amount</th>
                    <th>Invoice</th>
                </tr>
                </thead>
                <tbody>
                @forelse($bookings as $b)
                    <tr>
                        <td>{{ $b->venue->name ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($b->booking_date)->format('d/m/Y') }}</td>
                        <td>
                            @php $ts = optional($b->timeSlot); @endphp
                            {{ $ts->start_time ? \Carbon\Carbon::parse($ts->start_time)->format('h:i A') : '—' }}
                            to
                            {{ $ts->end_time ? \Carbon\Carbon::parse($ts->end_time)->format('h:i A') : '—' }}
                        </td>
                        <td>
    @php
        $s = strtolower($b->status ?? '');

        switch ($s) {
            case 'confirmed':
            case 'approved':
                $badgeClass = 'badge-success';
                $label = 'Confirmed';
                break;

            case 'pending':
                $badgeClass = 'badge-warn';
                $label = 'Pending';
                break;

            case 'rejected':
            case 'cancelled':
                $badgeClass = 'badge-danger';
                $label = ucfirst($s);
                break;

            default:
                $badgeClass = 'badge-secondary';
                $label = $b->status ? ucfirst($b->status) : ucfirst($b->status);
                break;
        }
    @endphp

    <span class="badge {{ $badgeClass }}">{{ $label }}</span>
</td>


                        {{-- NEW: Community --}}
                        <td>{{ ucfirst($b->community ?? 'non-dudhwala') }}</td>

                        {{-- NEW: Discount (stored on bookings table) --}}
                        <td>
                            @php $disc = (int)($b->discount ?? 0); @endphp
                            {{ $disc > 0 ? '₹'.number_format($disc) : '—' }}
                        </td>

                        {{-- Amount actually charged (payment amount = net after discount) --}}
                        <td>₹{{ number_format((float)($b->payment->amount ?? 0)) }}</td>

                        <td>
                            @if($b->payment?->id)
                                <a href="{{ route('book.invoice', $b->payment->id) }}" class="btn-pill" aria-label="Download invoice">
                                    Invoice
                                </a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">No bookings found</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- FLASH STATUS / ERRORS --}}
    @if (session('status'))
        <div class="status-alert mt-4">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert mt-4">{!! implode('<br>', $errors->all()) !!}</div>
    @endif

    {{-- PROFILE CARD (unchanged) --}}
    <div class="card mt-4" style="max-width: 720px; margin: 0 auto;">
        <div class="card-head">Update Profile</div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}" @submit="beforeSubmit">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Enter Name</label>
                    <input class="form-control" name="first_name"
                           value="{{ old('first_name', auth()->user()->first_name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Enter Surname</label>
                    <input class="form-control" name="last_name"
                           value="{{ old('last_name', auth()->user()->last_name) }}" required>
                </div>

                <div class="mb-2">
                    <label class="form-label">Mobile Number</label>
                    <div class="input-group">
                        <input type="tel" maxlength="10" class="form-control" id="contact_number" name="contact_number"
                               x-model="phone"
                               value="{{ old('contact_number', auth()->user()->contact_number) }}" required>
                        <button type="button" class="send-btn"
                                :disabled="sending || !canSend"
                                @click="sendOtp">
                            <template x-if="!sent"><span>Send OTP</span></template>
                            <template x-if="sent"><span>Resend (<span x-text="timer"></span>)</span></template>
                        </button>
                    </div>
                    <div class="help">Changing your mobile requires OTP verification.</div>
                    <div class="help" style="color:#b91c1c" x-text="error"></div>
                </div>

                <div class="mb-3" x-show="sent">
                    <label class="form-label">Enter OTP</label>
                    <div class="otp-boxes">
                        <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                               x-model="otp[0]" @input="digitsOnly($event); focusNext($event,1)">
                        <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                               x-model="otp[1]" @input="digitsOnly($event); focusNext($event,2)">
                        <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                               x-model="otp[2]" @input="digitsOnly($event); focusNext($event,3)">
                        <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                               x-model="otp[3]" @input="digitsOnly($event)">
                    </div>
                    <input type="hidden" name="otp" :value="otp.join('')">
                </div>

                <button class="action-primary w-100"
                        :disabled="submitting || (phoneChanged && otp.join('').length !== 4)">
                    <span x-show="!submitting">Update Profile</span>
                    <span x-show="submitting">Please wait…</span>
                </button>
            </form>
        </div>
    </div>
</div>


    {{-- Add a page class to scope layout overrides safely --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('profile-page');
        });
    </script>

    {{-- Alpine helpers (load Alpine via layout if you already do) --}}
    <script>
        function profilePage(){
            return {
                originalPhone: @json(auth()->user()->contact_number),
                phone: @json(old('contact_number', auth()->user()->contact_number)),
                otp: ['','','',''],
                sent: false,
                sending: false,
                submitting: false,
                timer: 0,
                timerId: null,
                error: '',
                get phoneChanged(){ return this.phone && this.phone !== this.originalPhone; },
                get canSend(){ return this.phoneChanged && /^\d{10}$/.test(this.phone) && this.timer === 0; },

                digitsOnly(e){ e.target.value = e.target.value.replace(/\D/g,'').slice(0,1); },

                focusNext(e, idx){
                    if(e.target.value && idx < 4){
                        const boxes = e.target.parentElement.querySelectorAll('.otp-box');
                        boxes[idx].focus();
                    }
                },

                async sendOtp(){
                    if(!this.canSend) return;
                    this.sending = true; this.error = '';
                    try{
                        const res = await fetch(@json(route('profile.phone.otp.send')), {
                            method:'POST',
                            headers:{
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ contact_number: this.phone })
                        });
                        const data = await res.json();

                        if(!res.ok){
                            this.error = data.message || 'Failed to send OTP.';
                            return;
                        }

                        if(data.status === 'noop'){
                            this.sent = false; this.timer = 0; this.otp = ['','','',''];
                            return;
                        }

                        this.sent = true;
                        this.timer = data.resend_after || 90;
                        this.startTimer();

                        if(data.dev_otp){ console.log('DEV OTP:', data.dev_otp); }
                    }catch(err){
                        this.error = 'Network error. Try again.';
                    }finally{
                        this.sending = false;
                    }
                },

                startTimer(){
                    if(this.timerId) clearInterval(this.timerId);
                    this.timerId = setInterval(() => {
                        if(this.timer > 0){ this.timer--; } else { clearInterval(this.timerId); this.timerId = null; }
                    }, 1000);
                },

                beforeSubmit(e){
                    this.submitting = true;
                    if(this.phoneChanged && this.otp.join('').length !== 4){
                        e.preventDefault();
                        this.submitting = false;
                        this.error = 'Enter the 4-digit OTP sent to your new number.';
                    }
                }
            }
        }
    </script>

    {{-- Load Alpine only if not already loaded in your base layout --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</x-app-layout>
