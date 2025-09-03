<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use App\Traits\SendsOtp;

class OtpRegisterController extends Controller
{
    use SendsOtp;

    // keep TTL aligned with the trait's 5 minutes (300s)
    private int $otpTtlSeconds   = 300;
    private int $cooldownSeconds = 90;  // resend cooldown
    private int $maxSends        = 3;   // per cooldown window

    public function showRegistrationForm(Request $request)
    {
        if (!session()->has('url.intended')) {
            session(['url.intended' => url()->previous()]);
        }
        return view('auth.register');
    }

    /**
     * Send / Resend OTP for Registration (AJAX JSON)
     * Flow:
     *  - Validate input
     *  - If verified user already exists => ask to login
     *  - Create (or reuse) an unverified user row for this phone
     *  - Rate-limit
     *  - Trait generates + stores OTP in otp_codes and sends SMS
     */
    public function sendRegisterOtp(Request $request)
    {
        $v = Validator::make($request->all(), [
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'contact_number' => 'required|digits:10',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status' => 'error',
                'code'   => 'validation',
                'errors' => $v->errors(),
            ], 422);
        }

        $first = (string) $request->string('first_name');
        $last  = (string) $request->string('last_name');
        $phone = (string) $request->string('contact_number');

        // keep for blade prefill
        session([
            'first_name'     => $first,
            'last_name'      => $last,
            'contact_number' => $phone,
        ]);

        // If a verified account already owns this number, suggest login
        if (User::where('contact_number', $phone)->where('is_verified', true)->exists()) {
            return response()->json([
                'status'    => 'error',
                'code'      => 'already_registered',
                'message'   => 'This mobile number is already registered.',
                'login_url' => route('otp.login.form'),
            ], 409);
        }

        // Ensure there is a user row to tie otp_codes.user_id to.
        // If a row exists but unverified, update names; otherwise create a fresh unverified record.
        $user = User::updateOrCreate(
            ['contact_number' => $phone],
            [
                'first_name'  => $first,
                'last_name'   => $last,
                'is_verified' => false,
                'role'        => 'user',
            ]
        );

        // Rate-limit by phone + IP
        $throttleKey = $this->throttleKey($phone, $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxSends)) {
            return response()->json([
                'status'      => 'error',
                'code'        => 'rate_limited',
                'message'     => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($throttleKey),
            ], 429);
        }

        // Generate, store, and send OTP via trait (writes to otp_codes with user_id)
        $this->sendOtpToMobile($phone);

        // Count this attempt (cooldown window)
        RateLimiter::hit($throttleKey, $this->cooldownSeconds);

        return response()->json([
            'status'       => 'ok',
            'message'      => 'OTP sent.',
            'resend_after' => $this->cooldownSeconds,
            'expires_in'   => $this->otpTtlSeconds, // ~300 seconds
        ], 201);
    }

    /**
     * Verify OTP and complete registration.
     * - Validates against otp_codes by user_id + code + expiry
     * - Deletes the OTP (single-use)
     * - Marks user as verified, logs them in
     */
    public function register(Request $request)
    {
        $request->validate([
            'contact_number' => 'required|digits:10',
            'otp'            => 'required|digits:4', // 4 digits to match trait
        ]);

        $phone = (string) $request->input('contact_number');
        $code  = (string) $request->input('otp');

        // Verified already?
        $already = User::where('contact_number', $phone)->where('is_verified', true)->first();
        if ($already) {
            // If already verified, just sign them in
            Auth::guard('web')->login($already);
            session()->forget(['first_name','last_name','contact_number']);
            return redirect()->intended(route('home'));
        }

        $user = User::where('contact_number', $phone)->first();
        if (! $user) {
            return back()->withErrors(['contact_number' => 'No pending registration found for this number.'])->withInput();
        }

        // Lookup OTP against otp_codes
        $otpRow = OtpCode::where('user_id', $user->id)
            ->where('otp', $code)
            ->where('expires_at', '>=', now())
            ->first();

        if (! $otpRow) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        DB::transaction(function () use ($otpRow, $user, $request) {
            // single-use OTP: delete it
            $otpRow->delete();

            // mark user verified and optionally update names from request (if provided)
            $user->first_name = $request->input('first_name', $user->first_name);
            $user->last_name  = $request->input('last_name', $user->last_name);
            $user->is_verified = true;
            if (empty($user->role)) {      // ✅ fallback for legacy rows
        $user->role = 'user';
    }
            $user->save();
        });

        // Clear rate limiter + session prefill
        RateLimiter::clear($this->throttleKey($phone, $request->ip()));
        session()->forget(['first_name','last_name','contact_number']);

        Auth::guard('web')->login($user);

        return redirect()->intended(route('home'));
    }

    private function throttleKey(string $phone, string $ip): string
    {
        return 'reg-otp:' . $phone . ':' . $ip;
    }
}
