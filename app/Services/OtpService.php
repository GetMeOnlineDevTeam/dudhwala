<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function generate(User $user): string
    {
        $otp = '1234'; // Use static OTP for dev

        OtpCode::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'is_used' => false,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // Replace this with SMS API later
        Log::info("Generated OTP $otp for user ID {$user->id}");

        return $otp;
    }

    public function validate(User $user, string $otp): bool
    {
        $otpCode = OtpCode::where('user_id', $user->id)
            ->where('otp', $otp)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($otpCode) {
            $otpCode->update(['is_used' => true]);
            return true;
        }

        return false;
    }
}
