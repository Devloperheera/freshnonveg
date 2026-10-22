<?php

namespace App\Services;

use App\Mail\UserOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class OtpDeliveryService
{
    /**
     * @return 'email'|'phone'
     */
    public function send(User $user, string $otp, string $channel, string $purpose): string
    {
        if ($channel === 'email') {
            $this->sendEmail($user, $otp, $purpose);

            return 'email';
        }

        if (empty($user->phone)) {
            throw ValidationException::withMessages([
                'login' => ['Is account par phone number registered nahi hai.'],
            ]);
        }

        if ($this->smsIsConfigured()) {
            $this->sendSms($user->phone, $otp, $purpose);

            return 'phone';
        }

        if (! empty($user->email)) {
            $this->sendEmail($user, $otp, $purpose);

            return 'email';
        }

        throw ValidationException::withMessages([
            'login' => ['Is number par SMS abhi available nahi hai. Registered email se OTP login karein.'],
        ]);
    }

    private function smsIsConfigured(): bool
    {
        return config('services.sms.driver') === 'msg91'
            && filled(config('services.sms.key'))
            && filled(config('services.sms.sender'));
    }

    private function sendEmail(User $user, string $otp, string $purpose): void
    {
        if (empty($user->email)) {
            throw ValidationException::withMessages([
                'login' => ['Is account par email registered nahi hai.'],
            ]);
        }

        try {
            Mail::to($user->email)->send(new UserOtpMail($otp, (string) ($user->name ?: 'Customer'), $purpose));
        } catch (Throwable $e) {
            Log::error('OTP email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            throw ValidationException::withMessages([
                'login' => ['OTP email nahi ja paya. Thodi der baad dubara try karein.'],
            ]);
        }
    }

    private function sendSms(string $phone, string $otp, string $purpose): void
    {
        Log::info('SMS OTP queued', ['phone' => $phone, 'purpose' => $purpose]);
    }
}
