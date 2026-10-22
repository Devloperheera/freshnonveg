<?php

namespace App\Services;

use App\Mail\UserOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OtpDeliveryService
{
    public function send(User $user, string $otp, string $channel, string $purpose): void
    {
        if ($channel === 'email') {
            if (empty($user->email)) {
                throw ValidationException::withMessages([
                    'login' => ['Is account par email registered nahi hai.'],
                ]);
            }

            Mail::to($user->email)->send(new UserOtpMail($otp, $user->name, $purpose));

            return;
        }

        if (empty($user->phone)) {
            throw ValidationException::withMessages([
                'login' => ['Is account par phone number registered nahi hai.'],
            ]);
        }

        $this->sendSms($user->phone, $otp, $purpose);
    }

    private function sendSms(string $phone, string $otp, string $purpose): void
    {
        $message = match ($purpose) {
            'login' => "FreshNonVeg login OTP: {$otp}. Valid for 10 minutes.",
            'reset' => "FreshNonVeg password reset OTP: {$otp}. Valid for 10 minutes.",
            default => "FreshNonVeg OTP: {$otp}",
        };

        if (config('services.sms.driver') === 'log' || config('app.debug')) {
            Log::info('SMS OTP', ['phone' => $phone, 'message' => $message]);

            return;
        }

        // Future: MSG91 / Twilio integration via env
        Log::warning('SMS driver not configured; OTP logged only.', ['phone' => $phone]);
    }
}
