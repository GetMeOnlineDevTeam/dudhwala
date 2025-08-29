<x-guest-layout>
    <style>
        :root {
            --brand: #116631;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --bg: #f7f7f7;
        }

        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            padding: 24px
        }

        .card {
            position: relative;
            width: 100%;
            max-width: 420px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .06);
            padding: 28px
        }

        .badge {
            position: absolute;
            left: 50%;
            top: -40px;
            transform: translateX(-50%);
            width: 96px;
            height: 96px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
            display: grid;
            place-items: center;
            border: 4px solid #fff
        }

        .badge img {
            width: 88px;
            height: 88px;
            object-fit: contain
        }

        h1 {
            margin-top: 36px;
            margin-bottom: 6px;
            font-size: 20px;
            color: var(--text);
            text-align: center;
            font-weight: 600
        }

        .sub {
            font-size: 12px;
            color: var(--muted);
            text-align: center;
            margin-bottom: 12px
        }

        .group {
            margin: 16px 0
        }

        .label {
            font-size: 13px;
            color: var(--text);
            margin-bottom: 6px
        }

        .row2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px
        }

        .row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center
        }

        .input {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 15px;
            outline: none;
            transition: border .15s
        }

        .input:focus {
            border-color: #c7d9cf;
            box-shadow: 0 0 0 3px rgba(17, 102, 49, .08)
        }

        input:invalid {
            box-shadow: none
        }

        /* neutral placeholders + text */
        .input::placeholder,.input:focus::placeholder,.input:invalid::placeholder,.input[aria-invalid="true"]::placeholder {
            color: #9CA3AF !important;
            opacity: 1 !important
        }

        .input,.input:focus,.input:invalid,.input[aria-invalid="true"] {
            color: #1f2937 !important
        }

        .send-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 44px;
            min-width: 120px;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            background: #fff;
            border: 1px solid var(--brand);
            color: var(--brand)
        }

        .send-btn:disabled {
            opacity: .55;
            cursor: not-allowed
        }

        .chip {
            height: 32px;
            padding: 0 10px;
            border-radius: 8px;
            border: 1px solid var(--brand);
            color: var(--brand);
            background: #fff;
            font-size: 12px
        }

        /* OTP UI */
        .otp-wrap {
            position: relative;
            margin-top: 8px
        }

        .otp-track {
            display: grid;
            grid-template-columns: repeat(4, 56px);
            justify-content: center;
            gap: 12px;
            margin: 0 auto;
            width: max-content;
            cursor: text
        }

        .otp-box {
            width: 56px;
            height: 56px;
            border: 1px dashed var(--border);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: var(--text);
            background: #fafafa;
            position: relative
        }

        .otp-box.caret::after {
            content: "";
            width: 2px;
            height: 22px;
            background: var(--brand);
            display: block;
            animation: blink 1s step-end infinite
        }

        @keyframes blink {
            50% {
                opacity: 0
            }
        }

        .otp-hidden {
            position: absolute;
            inset: 0;
            opacity: 0;
            border: 0
        }

        .hint {
            font-size: 12px;
            color: #8a6b2a;
            margin-top: 6px;
            text-align: center
        }

        .error-inline {
            font-size: 12px;
            color: #8a6b2a;
            text-align: left;
            margin-top: 6px
        }

        .error-inline a {
            color: #2563eb !important;
            /* blue */
            text-decoration: underline;
            font-weight: 600;
            white-space: nowrap;
            /* keep "Log in" on one line */
        }

        .error-row {
            display: flex;
            gap: 6px;
            align-items: flex-start;
            justify-content: flex-start
        }

        .cta {
            margin-top: 14px
        }

        .btn {
            width: 100%;
            background: var(--brand);
            color: #fff;
            border: 0;
            border-radius: 12px;
            padding: 12px 0;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer
        }

        .btn:disabled {
            opacity: .6;
            cursor: not-allowed
        }

        .foot {
            margin-top: 14px;
            text-align: center;
            font-size: 12px;
            color: #6b7280
        }

        .foot a {
            color: var(--brand);
            text-decoration: underline
        }
    </style>

    <div class="wrap">
        <div class="card">
            <div class="badge"><img src="{{ asset('storage/logo/logo.png') }}" alt="Logo"></div>
            <h1>Register with OTP</h1>
            <p class="sub">Enter your details and mobile number to receive a verification code.</p>

            <div x-data="RegisterOtpController()" x-init="init(
                @json(old('first_name', session('first_name'))),
                @json(old('last_name', session('last_name'))),
                @json(old('contact_number', session('contact_number'))),
                {{ $errors->has('contact_number') ? 'true' : 'false' }},
                {{ $errors->has('otp') ? 'true' : 'false' }}
            )">
                <!-- Names -->
                <div class="group">
                    <div class="row2">
                        <div>
                            <div class="label">First Name</div>
                            <input class="input" type="text" x-model="firstName" name="first_name"
                                placeholder="Enter Name" :aria-invalid="fNameError ? 'true' : 'false'"
                                @input="fNameError=''">
                            @error('first_name')
                                <div class="error-inline">{{ $message }}</div>
                            @enderror
                            <div class="error-inline" x-show="fNameError" x-text="fNameError"></div>
                        </div>
                        <div>
                            <div class="label">Last Name</div>
                            <input class="input" type="text" x-model="lastName" name="last_name"
                                placeholder="Enter Surname" :aria-invalid="lNameError ? 'true' : 'false'"
                                @input="lNameError=''">
                            @error('last_name')
                                <div class="error-inline">{{ $message }}</div>
                            @enderror
                            <div class="error-inline" x-show="lNameError" x-text="lNameError"></div>
                        </div>
                    </div>
                </div>

                <!-- Phone -->
                <div class="group">
                    <div class="label">Mobile Number</div>
                    <div class="row">
                        <input class="input" x-ref="phone" x-model="contact" name="contact_number" type="tel"
                            inputmode="numeric" maxlength="10" :readonly="otpSent" placeholder="10-digit number"
                            :aria-invalid="phoneError ? 'true' : 'false'"
                            @input="contact = onlyDigits(contact,10); phoneError=''; suggestLogin=false">
                        <button type="button" class="send-btn" :disabled="!canSend || sending" @click="onSend"
                            x-text="cooldownLeft ? `Resend ${mmss}` : (otpSent ? 'Resend OTP' : (sending ? 'Sending…' : 'Send OTP'))"></button>
                    </div>

                    @error('contact_number')
                        <div class="error-inline">{{ $message }}</div>
                    @enderror
                    <div class="error-inline error-row" x-show="phoneError">
                        <span x-text="phoneError"></span>
                        <a :href="loginUrl" x-show="suggestLogin">Log in</a>
                    </div>

                    <div style="margin-top:6px;text-align:right" x-show="otpSent">
                        <button class="chip" type="button" @click="editNumber">Change</button>
                    </div>
                </div>

                <!-- OTP -->
                <div class="group">
                    <div class="label">OTP Verification</div>
                    <div class="otp-wrap" @click="$refs.otp.focus()">
                        <div class="otp-track">
                            <template x-for="i in otpLen" :key="i">
                                <div class="otp-box"
                                    :class="{ 'caret': otpFocused && otp.length === (i - 1) && otp.length < otpLen }"
                                    x-text="otp[i-1] || ''"></div>
                            </template>
                        </div>
                        <input class="otp-hidden" x-ref="otp" x-model="otp" type="text" name="otp"
                            inputmode="numeric" :maxlength="otpLen" :disabled="!otpSent" @focus="otpFocused = true"
                            @blur="otpFocused = false" @input="otp = onlyDigits(otp, otpLen)"
                            @paste.prevent="onOtpPaste($event)">
                    </div>
                    <div class="hint" x-show="otpSent">
                        <span
                            x-text="cooldownLeft ? `Didn’t get OTP? Wait ${mmss}` : 'Didn’t get OTP? You can resend now.'"></span>
                    </div>
                    @error('otp')
                        <div class="error-inline">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit -->
                <form method="POST" action="{{ route('otp.register') }}" class="cta" novalidate>
                    @csrf
                    <input type="hidden" name="first_name" :value="firstName">
                    <input type="hidden" name="last_name" :value="lastName">
                    <input type="hidden" name="contact_number" :value="contact">
                    <input type="hidden" name="otp" :value="otp">
                    <button class="btn" type="submit" :disabled="otp.length !== otpLen">Register</button>
                </form>

                <div class="foot">Already have an account? <a href="{{ route('otp.login.form') }}">Log in</a></div>
            </div>
        </div>
    </div>

    <script>
        function RegisterOtpController() {
            return {
                firstName: '',
                lastName: '',
                contact: '',
                otp: '',
                fNameError: '',
                lNameError: '',
                phoneError: '',
                suggestLogin: false,
                loginUrl: '{{ route('otp.login.form') }}',
                otpSent: false,
                otpFocused: false,
                sending: false,
                otpLen: 4,
                cooldownSec: 90,
                until: 0,
                now: Date.now(),

                get cooldownLeft() {
                    return Math.max(0, Math.floor((this.until - this.now) / 1000));
                },
                get canSend() {
                    return this.firstName?.length > 0 && this.lastName?.length > 0 && this.contact?.length === 10 &&
                        this.cooldownLeft === 0;
                },
                get mmss() {
                    const s = this.cooldownLeft,
                        m = String(Math.floor(s / 60)).padStart(2, '0'),
                        ss = String(s % 60).padStart(2, '0');
                    return `${m}:${ss}`;
                },

                onlyDigits(v, max) {
                    return String(v || '').replace(/\D/g, '').slice(0, max);
                },

                // f,l,phone from old/session; hadContactError & hadOtpError from backend validation
                init(f, l, phone, hadContactError, hadOtpError) {
                    this.firstName = f || '';
                    this.lastName = l || '';
                    this.contact = this.onlyDigits(phone || '', 10);

                    if (hadContactError) {
                        this.otpSent = false; // invalid phone -> stay on phone step
                    } else {
                        this.otpSent = !!hadOtpError; // keep OTP step active after wrong OTP
                    }

                    this.until = 0; // timer doesn't persist
                    setInterval(() => {
                        this.now = Date.now();
                    }, 500);

                    this.$nextTick(() => (this.otpSent ? this.$refs.otp?.focus() : this.$refs.phone?.focus()));
                },

                async onSend() {
                    if (!this.canSend || this.sending) return;
                    this.sending = true;

                    // clear inline errors
                    this.fNameError = this.lNameError = this.phoneError = '';
                    this.suggestLogin = false;

                    try {
                        const res = await fetch(`{{ route('otp.register.send') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'Accept': 'application/json', // ensure JSON
                                'X-Requested-With': 'XMLHttpRequest', // hint to Laravel
                                'X-CSRF-TOKEN': `{{ csrf_token() }}`
                            },
                            body: new URLSearchParams({
                                first_name: this.firstName,
                                last_name: this.lastName,
                                contact_number: this.contact
                            })
                        });

                        // robust parse (JSON or text)
                        const ct = res.headers.get('content-type') || '';
                        let data = {};
                        let text = '';
                        try {
                            if (ct.includes('application/json')) data = await res.json();
                            else text = await res.text();
                        } catch (_) {}

                        if (res.ok) {
                            this.otpSent = true;
                            this.startCooldown();
                            this.$nextTick(() => {
                                this.$refs.otp?.focus();
                                this.otpFocused = true;
                            });
                            return;
                        }

                        // 409: already registered
                        if (res.status === 409 || data.code === 'already_registered' || /already registered/i.test(
                                text)) {
                            this.phoneError = data.message ||
                                'This mobile number is already registered. Please ';
                            this.suggestLogin = true;
                            this.loginUrl = data.login_url || this.loginUrl;
                        }
                        // 422: validation errors
                        else if (res.status === 422 && (data.errors || '').toString !== undefined) {
                            const errs = data.errors || {};
                            this.fNameError = (errs.first_name || [])[0] || '';
                            this.lNameError = (errs.last_name || [])[0] || '';
                            this.phoneError = (errs.contact_number || [])[0] || (data.message || 'Invalid input.');
                        }
                        // 429: rate limit
                        else if (res.status === 429) {
                            const sec = Number(data.retry_after || 60);
                            this.phoneError = data.message || `Too many requests. Please try again in ${sec}s.`;
                            this.until = Date.now() + sec * 1000;
                        }
                        // fallback
                        else {
                            this.phoneError = data.message || 'Could not send OTP. Please try again.';
                        }

                        // stay on phone step and focus
                        this.otpSent = false;
                        this.$nextTick(() => this.$refs.phone?.focus());
                    } catch {
                        this.phoneError = 'Network error. Please try again.';
                        this.otpSent = false;
                    } finally {
                        this.sending = false;
                    }
                },

                onOtpPaste(e) {
                    const d = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, this.otpLen);
                    if (d) {
                        this.otp = d;
                    }
                },

                startCooldown() {
                    this.until = Date.now() + (this.cooldownSec * 1000);
                },

                editNumber() {
                    this.otpSent = false;
                    this.otp = '';
                    this.until = 0;
                    this.$nextTick(() => this.$refs.phone?.focus());
                }
            }
        }
    </script>
</x-guest-layout>
