<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** Tune these to match your login/registration controllers */
    private int $otpTtlSeconds   = 600; // 10 min
    private int $cooldownSeconds = 90;  // resend cooldown
    private int $maxSends        = 3;   // per cooldown window

    /**
     * Profile page.
     */ 
    public function edit(Request $request): View
    {
        $bookings = Bookings::with(['payment', 'venue', 'timeSlot'])
            ->where('user_id', $request->user()->id)
            ->whereNotNull('payment_id')
            ->latest('created_at')
            ->get();

        return view('profile.edit', [
            'user'     => $request->user(),
            'bookings' => $bookings,
        ]);
    }

    /**
     * Send/resend OTP for changed phone (JSON).
     * Mirrors the approach in your OtpLoginController.
     */
    public function sendPhoneOtp(Request $request): JsonResponse
    {
        $request->validate([
            'contact_number' => 'required|digits:10',
        ]);

        $user  = $request->user();
        $phone = $request->input('contact_number');

        // No OTP needed if number didn’t change
        if ($phone === $user->contact_number) {
            return response()->json([
                'status'  => 'noop',
                'message' => 'Phone unchanged; OTP not required.',
            ], 200);
        }

        // Don’t allow switching to a number already verified by someone else
        $usedByAnother = User::where('contact_number', $phone)
            ->where('id', '!=', $user->id)
            ->where('is_verified', true)
            ->exists();

        if ($usedByAnother) {
            return response()->json([
                'status'  => 'error',
                'message' => 'This mobile number is already in use.',
            ], 409);
        }

        // Rate limit by user + phone + IP
        $throttleKey = $this->throttleKey($user->id, $phone, $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxSends)) {
            return response()->json([
                'status'      => 'error',
                'message'     => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($throttleKey),
            ], 429);
        }

        // Single active OTP for this user: clear old rows
        OtpCode::where('user_id', $user->id)->delete();

        // Generate OTP (use fixed 1234 on local like your other controllers)
        $otp       = app()->environment('local') ? '1234' : (string) random_int(1000, 9999);
        $expiresAt = now()->addSeconds($this->otpTtlSeconds);

        OtpCode::create([
            'user_id'    => $user->id,
            'otp'        => $otp,      // for higher security, store a hash and verify with Hash::check
            'is_used'    => false,
            'expires_at' => $expiresAt,
        ]);

        // Remember which phone we’re verifying; prevents swapping number post-send
        session(['profile.pending_phone' => $phone]);

        // TODO: hook your SMS provider here
        // Sms::send($phone, "Your verification OTP is {$otp}");

        RateLimiter::hit($throttleKey, $this->cooldownSeconds);

        return response()->json([
            'status'       => 'ok',
            'message'      => 'OTP sent.',
            'resend_after' => $this->cooldownSeconds,
            'expires_in'   => $expiresAt->diffInSeconds(now()),
            'dev_otp'      => app()->environment('local') ? $otp : null,
        ], 201);
    }

    /**
     * Update name/surname always; phone only after OTP verify (if changed).
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'contact_number' => 'required|digits:10',
            // 'otp' validated conditionally below if phone changed
        ]);

        $user       = $request->user();
        $newPhone   = $request->input('contact_number');
        $phoneChanged = $newPhone !== $user->contact_number;

        // If user is trying to switch to someone else’s verified number, block
        if ($phoneChanged) {
            $usedByAnother = User::where('contact_number', $newPhone)
                ->where('id', '!=', $user->id)
                ->where('is_verified', true)
                ->exists();

            if ($usedByAnother) {
                return back()->withErrors(['contact_number' => 'This mobile number is already in use.'])->withInput();
            }

            // Must match the pending phone the OTP was sent to
            $pending = session('profile.pending_phone');
            if (!$pending || $pending !== $newPhone) {
                return back()->withErrors(['contact_number' => 'Please request OTP for this number again.'])->withInput();
            }

            // Require and verify OTP
            $request->validate(['otp' => 'required|digits:4']);

            $otpRow = OtpCode::where('user_id', $user->id)
                ->where('otp', $request->input('otp'))
                ->where('is_used', false)
                ->where('expires_at', '>=', now())
                ->first();

            if (!$otpRow) {
                return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
            }

            DB::transaction(function () use ($otpRow) {
                // single-use
                $otpRow->is_used = true;
                $otpRow->save();
                // alternatively: $otpRow->delete();
            });
        }

        // Save profile
        $user->first_name = (string) $request->string('first_name');
        $user->last_name  = (string) $request->string('last_name');

        if ($phoneChanged) {
            $user->contact_number = $newPhone;
            $user->is_verified    = true;  // mark phone verified
            session()->forget('profile.pending_phone');
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'Profile updated successfully!');
    }

    /**
     * Optional: keep your existing account deletion.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function throttleKey(int $userId, string $phone, string $ip): string
    {
        return "profile-otp:{$userId}:{$phone}:{$ip}";
    }
}
