<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class OtpLoginController extends Controller
{
    // tune here
    private int $otpTtlSeconds   = 300; // 5 minutes
    private int $cooldownSeconds = 90;  // resend cooldown
    private int $maxSends        = 3;   // per cooldown window

    /**
     * Show the OTP login form.
     * Keep intended URL for post-login redirect.
     */
    public function showLoginForm(Request $request)
    {
        if (! session()->has('url.intended')) {
            session(['url.intended' => url()->previous()]);
        }

        // view path unchanged
        return view('auth.login');
    }

    /**
     * Send / Resend OTP (AJAX). Returns JSON.
     */
    public function sendLoginOtp(Request $request)
    {
        $request->validate([
            'contact_number' => 'required|digits:10',
        ]);

        $phone = $request->input('contact_number');

        $user = User::where('contact_number', $phone)->first();
        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User not found. Register to continue.',
            ], 404);
        }

        // Rate-limit by phone + IP
        $throttleKey = $this->throttleKey($phone, $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxSends)) {
            return response()->json([
                'status'      => 'error',
                'message'     => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($throttleKey),
            ], 429);
        }

        // Ensure single active OTP: delete any existing rows for this user
        OtpCode::where('user_id', $user->id)->delete();

        // Create fresh OTP
        // $otp       = (string) random_int(1000, 9999);
        $otp = '1234';
        $expiresAt = now()->addSeconds($this->otpTtlSeconds);

        OtpCode::create([
            'user_id'    => $user->id,
            'otp'        => $otp,           // for higher security, store a hash and verify with Hash::check
            'expires_at' => $expiresAt,
            'is_used'    => false,          // kept for compatibility; we delete on verify
        ]);

        // TODO: integrate SMS provider here
        // SmsService::send($phone, "Your OTP is {$otp}");

        // count this attempt (cooldown window)
        RateLimiter::hit($throttleKey, $this->cooldownSeconds);

        return response()->json([
            'status'       => 'ok',
            'message'      => 'OTP sent.',
            'resend_after' => $this->cooldownSeconds,
            'expires_in'   => $expiresAt->diffInSeconds(now()), // ~300
        ], 201);
    }

    /**
     * Verify OTP and log the user in.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'contact_number' => 'required|digits:10',
            'otp'            => 'required|digits:4',
        ]);

        $phone = $request->input('contact_number');
        $code  = $request->input('otp');

        $user = User::where('contact_number', $phone)->first();
        if (! $user) {
            return back()->withErrors(['contact_number' => 'User not found.'])->withInput();
        }

        // Find valid OTP
        $otpRow = OtpCode::where('user_id', $user->id)
            ->where('otp', $code)
            ->where('expires_at', '>=', now())
            ->first();

        if (! $otpRow) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        DB::transaction(function () use ($otpRow, $user) {
            // Delete OTP row (single-use)
            $otpRow->delete();

            // If you want to flip a login flag, do it here. We just auth the user.
            // $user->last_login_at = now(); $user->save();
        });

        Auth::login($user);

        return redirect()->intended(route('home'));
    }

    private function throttleKey(string $phone, string $ip): string
    {
        return 'login-otp:' . $phone . ':' . $ip;
    }
}
