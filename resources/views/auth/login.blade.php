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

        /* neutral placeholder + text */
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
            cursor: pointer;
            border-radius: 10px;
            background: #fff;
            border: 1px solid var(--brand);
            color: var(--brand)
        }

        .send-btn:disabled {
            opacity: .55;
            cursor: not-allowed
        }

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

        .error-inline{font-size:12px;color:#8a6b2a;text-align:left;margin-top:6px}
.error-row{display:flex;gap:6px;justify-content:flex-start;align-items:flex-start;width:100%}

        .error-row a {
            color: var(--brand);
            text-decoration: underline
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
            <h1>Sign in with OTP</h1>
            <p class="sub">Enter your mobile number and we’ll send a verification code.</p>

            <div x-data="LoginOtpController()" x-init="init(
                {{ session('otp_sent') ? 'true' : 'false' }},
                '{{ old('contact_number', session('contact_number')) }}',
                {{ $errors->has('contact_number') ? 'true' : 'false' }},
                {{ $errors->has('otp') ? 'true' : 'false' }}
            )">

                <!-- Phone -->
                <div class="group">
                    <div class="label">Mobile Number</div>
                    <div class="row">
                        <input class="input" x-ref="phone" x-model="contact" name="contact_number" type="tel"
                            inputmode="numeric" maxlength="10" :readonly="otpSent" placeholder="10-digit number"
                            :aria-invalid="phoneError ? 'true' : 'false'"
                            @input="contact = onlyDigits(contact,10); phoneError=''; suggestRegister=false;">
                        <button type="button" class="send-btn" :disabled="!canSend || sending" @click="onSend"
                            x-text="cooldownLeft ? `Resend ${mmss}` : (otpSent ? 'Resend OTP' : (sending ? 'Sending…' : 'Send OTP'))"></button>
                    </div>

                    <!-- Server-side Blade errors -->
                    <x-input-error :messages="$errors->get('contact_number')" class="error-inline" />

                    <!-- Inline API error -->
                    <div class="error-inline error-row" x-show="phoneError">
                        <span x-text="phoneError"></span>
                        <a href="{{ route('otp.register.form') }}" x-show="suggestRegister">Register now</a>
                    </div>

                    <div style="margin-top:6px;text-align:right" x-show="otpSent">
                        <button class="send-btn" style="height:32px;min-width:unset;padding:0 10px;border-radius:8px"
                            type="button" @click="editNumber">Change</button>
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
                    <x-input-error :messages="$errors->get('otp')" class="error-inline" />
                </div>

                <!-- Submit -->
                <form method="POST" action="{{ route('otp.login') }}" class="cta" novalidate>
                    @csrf
                    <input type="hidden" name="contact_number" :value="contact">
                    <input type="hidden" name="otp" :value="otp">
                    <button class="btn" type="submit" :disabled="otp.length !== otpLen">Log in</button>
                </form>

                <div class="foot">Don’t have an account? <a href="{{ route('otp.register.form') }}">Register now</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function LoginOtpController() {
            return {
                otpSent: false,
                contact: '',
                otp: '',
                otpLen: 4,
                cooldownSec: 90,
                until: 0,
                now: Date.now(),
                otpFocused: false,
                sending: false,
                phoneError: '',
                suggestRegister: false,

                get cooldownLeft() {
                    return Math.max(0, Math.floor((this.until - this.now) / 1000));
                },
                get canSend() {
                    return this.contact?.length === 10 && this.cooldownLeft === 0;
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

                init(sent, prefill, hadContactError, hadOtpError) {
                    this.contact = this.onlyDigits(prefill || '', 10);

                    if (hadContactError) {
                        this.otpSent = false;
                    } else {
                        this.otpSent = !!sent || !!hadOtpError;
                    }

                    this.until = 0;
                    setInterval(() => {
                        this.now = Date.now();
                    }, 500);

                    this.$nextTick(() => {
                        (this.otpSent ? this.$refs.otp?.focus() : this.$refs.phone?.focus());
                    });
                },

                async onSend() {
                    if (!this.canSend || this.sending) return;
                    this.sending = true;
                    this.phoneError = '';
                    this.suggestRegister = false;

                    try {
                        const res = await fetch(`{{ route('otp.login.send') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-TOKEN': `{{ csrf_token() }}`
                            },
                            body: new URLSearchParams({
                                contact_number: this.contact
                            })
                        });

                        let data = {};
                        try {
                            data = await res.json();
                        } catch (e) {}

                        if (res.ok) {
                            this.otpSent = true;
                            this.startCooldown();
                            this.$nextTick(() => {
                                this.$refs.otp?.focus();
                                this.otpFocused = true;
                            });
                        } else {
                            // 404: user not found -> inline message + Register link
                            if (res.status === 404) {
                                this.phoneError = data.message || 'User not found. Register to continue.';
                                this.suggestRegister = true;
                            }
                            // 429: rate limit -> show retry seconds & start a local cooldown
                            else if (res.status === 429) {
                                const sec = Number(data.retry_after || 60);
                                this.phoneError = data.message || `Please wait ${sec}s before trying again.`;
                                this.until = Date.now() + sec * 1000;
                            }
                            // 422: validation
                            else if (res.status === 422) {
                                this.phoneError = (data.errors && data.errors.contact_number && data.errors
                                        .contact_number[0]) ||
                                    data.message || 'Invalid input.';
                            }
                            // other errors
                            else {
                                this.phoneError = data.message || 'Could not send OTP. Please try again.';
                            }

                            // stay on phone step and focus it
                            this.otpSent = false;
                            this.$nextTick(() => this.$refs.phone?.focus());
                        }
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
