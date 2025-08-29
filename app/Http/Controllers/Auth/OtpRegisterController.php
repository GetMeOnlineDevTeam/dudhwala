<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

class OtpRegisterController extends Controller
{
    public function showRegistrationForm(Request $request)
    {
        if (!session()->has('url.intended')) {
            session(['url.intended' => url()->previous()]);
        }
        return view('auth.register');
    }

    // Send / Resend OTP (AJAX, JSON only)
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
        session(['first_name' => $first, 'last_name' => $last, 'contact_number' => $phone]);

        // If a verified account already owns this number, suggest login
        if ($existing = User::where('contact_number', $phone)->first()) {
            if ($existing->is_verified) {
                return response()->json([
                    'status'    => 'error',
                    'code'      => 'already_registered',
                    'message'   => 'This mobile number is already registered.',
                    'login_url' => route('otp.login.form'),
                ], 409);
            }
        }

        // Throttle (3 attempts, 90s cooldown)
        $throttleKey = "reg-otp:{$phone}:".$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return response()->json([
                'status'      => 'error',
                'code'        => 'rate_limited',
                'message'     => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($throttleKey),
            ], 429);
        }

        // Generate & stash OTP + form data for 5 minutes
        $otp = app()->environment('local') ? '1234' : (string) random_int(1000, 9999);
        $ttl = 300; // seconds

        Cache::put("reg:{$phone}", [
            'otp'        => $otp,
            'first_name' => $first,
            'last_name'  => $last,
        ], $ttl);

        RateLimiter::hit($throttleKey, 90);

        // TODO: send SMS with $otp

        return response()->json([
            'status'       => 'ok',
            'message'      => 'OTP sent.',
            'resend_after' => 90,
            'expires_in'   => $ttl,
            // 'dev_otp'    => app()->environment('local') ? $otp : null,
        ], 201);
    }

    // Verify OTP and complete registration (create/update user here)
    public function register(Request $request)
    {
        $request->validate([
            'contact_number' => 'required|digits:10',
            'otp'            => 'required|digits:4',
        ]);

        $phone = $request->input('contact_number');
        $otp   = $request->input('otp');

        $pending = Cache::get("reg:{$phone}");
        if (!$pending) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }
        if (!hash_equals((string) $pending['otp'], (string) $otp)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        // In case someone verified this number meanwhile
        if (User::where('contact_number', $phone)->where('is_verified', true)->exists()) {
            return back()->withErrors(['contact_number' => 'This mobile number is already registered.'])->withInput();
        }

        $user = User::updateOrCreate(
            ['contact_number' => $phone],
            [
                'first_name'  => $pending['first_name'] ?? $request->input('first_name'),
                'last_name'   => $pending['last_name']  ?? $request->input('last_name'),
                'is_verified' => true,
            ]
        );

        Cache::forget("reg:{$phone}");
        RateLimiter::clear("reg-otp:{$phone}:".$request->ip()); // optional reset

        Auth::login($user);
        session()->forget(['first_name','last_name','contact_number']);

        return redirect()->intended(route('home'));
    }
}
