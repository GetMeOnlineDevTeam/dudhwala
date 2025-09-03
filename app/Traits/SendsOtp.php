<?php 
namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

trait SendsOtp
{
    protected function sendOtpToMobile(string $phone): void
    {
        $phone = trim($phone);

        // Find the user by phone (contact_number)
        $user = User::where('contact_number', $phone)->first();
        if (! $user) {
            // Controller should already guard this, but stay safe.
            Log::warning("sendOtpToMobile: user not found for phone {$phone}");
            return;
        }

        // Create fresh 4-digit OTP and expiry (5 minutes)
        // $otp       = (string) random_int(1000, 9999);
        $otp = 1234;
        $expiresAt = now()->addSeconds(300); // matches controller's 5 minutes

        // Ensure single active OTP per user
        DB::table('otp_codes')->where('user_id', $user->id)->delete();

        // Insert the new OTP row
        DB::table('otp_codes')->insert([
            'user_id'    => $user->id,
            'otp'        => $otp,
            'is_used'    => false,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ---- Send via Fast2SMS (remove this if you only want DB write) ----
        try {
            // Http::get('https://www.fast2sms.com/dev/bulkV2', [
            //     'authorization'    => '9nCC17JFby5vfzh47tVrm68mSnHDuYhCqnzhvj2faZ4zQy4ZLPIcUu9VT8bX',
            //     'route'            => 'dlt',
            //     'sender_id'        => 'GETMEO',
            //     'message'          => '176965',
            //     'variables_values' => $otp . '|',  // keep '|' if template expects more vars
            //     'numbers'          => $phone,      // if your account needs ISD: '91'.$phone
            //     'flash'            => '0',
            // ]);
        } catch (\Throwable $e) {
            Log::error('Fast2SMS exception: '.$e->getMessage());
            // Don't throw — the OTP is stored; user can resend after cooldown.
        }
    }

}
