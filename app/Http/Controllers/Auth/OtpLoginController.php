<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use App\Traits\SendsOtp;

class OtpLoginController extends Controller
{
    use SendsOtp;

    private int $otpTtlSeconds   = 300; // 5 minutes
    private int $cooldownSeconds = 90;  // resend cooldown
    private int $maxSends        = 3;   // per cooldown window

    public function showLoginForm(Request $request)
    {
        if (! session()->has('url.intended')) {
            session(['url.intended' => url()->previous()]);
        }
        return view('auth.login');
    }

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

        // Generate, store, and send OTP via trait (writes to otp_codes with user_id)
        $this->sendOtpToMobile($phone);

        // Count this attempt in the cooldown window
        RateLimiter::hit($throttleKey, $this->cooldownSeconds);

        return response()->json([
            'status'       => 'ok',
            'message'      => 'OTP sent.',
            'resend_after' => $this->cooldownSeconds,
            'expires_in'   => $this->otpTtlSeconds,
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'contact_number' => 'required|digits:10',
            'otp'            => 'required|digits:4', // 4-digit to match trait
        ]);

        $phone = $request->input('contact_number');
        $code  = $request->input('otp');

        $user = User::where('contact_number', $phone)->first();
        if (! $user) {
            return back()->withErrors(['contact_number' => 'User not found.'])->withInput();
        }

        // Check valid OTP from otp_codes
        $otpRow = OtpCode::where('user_id', $user->id)
            ->where('otp', $code)
            ->where('expires_at', '>=', now())
            ->first();

        if (! $otpRow) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        DB::transaction(function () use ($otpRow) {
            // single-use: delete it (or set is_used = true)
            $otpRow->delete();
        });

        Auth::login($user);

        return redirect()->intended(route('home'));
    }

    private function throttleKey(string $phone, string $ip): string
    {
        return 'login-otp:' . $phone . ':' . $ip;
    }
}
