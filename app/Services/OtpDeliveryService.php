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
    public function __construct(private Fast2SmsService $fast2sms) {}

    /**
     * @return 'email'|'phone'
     */
    public function send(User $user, string $otp, string $channel, string $purpose, string $loginDestination): string
    {
        if ($channel === 'email') {
            $this->sendEmail($user, $otp, $purpose, $this->resolveEmailDestination($loginDestination, $user));

            return 'email';
        }

        $mobile = $this->resolvePhoneDestination($loginDestination);
        if ($mobile === null) {
            throw ValidationException::withMessages([
                'login' => ['Valid 10-digit mobile number enter karein.'],
            ]);
        }

        if ($this->fast2sms->isConfigured()) {
            $this->sendOtpSms($mobile, $otp, $purpose);

            return 'phone';
        }

        throw ValidationException::withMessages([
            'login' => ['SMS gateway configure nahi hai. Admin se contact karein ya email se try karein.'],
        ]);
    }

    private function sendEmail(User $user, string $otp, string $purpose, string $toEmail): void
    {
        if ($toEmail === '') {
            throw ValidationException::withMessages([
                'login' => ['Is account par email registered nahi hai.'],
            ]);
        }

        try {
            Mail::to($toEmail)->send(new UserOtpMail($otp, (string) ($user->name ?: 'Customer'), $purpose));
        } catch (Throwable $e) {
            Log::error('OTP email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            throw ValidationException::withMessages([
                'login' => ['OTP email nahi ja paya. Thodi der baad dubara try karein.'],
            ]);
        }
    }

    private function sendOtpSms(string $mobile10, string $otp, string $purpose): void
    {
        $result = $this->fast2sms->sendOtp($mobile10, $otp);

        if ($result['ok']) {
            return;
        }

        Log::warning('Fast2SMS OTP rejected', ['purpose' => $purpose, 'error' => $result['error']]);

        throw ValidationException::withMessages([
            'login' => ['OTP SMS fail ho gaya. Number sahi hai aur DLT template active hai check karein.'],
        ]);
    }

    private function resolveEmailDestination(string $loginDestination, User $user): string
    {
        $loginDestination = trim($loginDestination);
        if (filter_var($loginDestination, FILTER_VALIDATE_EMAIL)) {
            return strtolower($loginDestination);
        }

        $accountEmail = strtolower(trim((string) ($user->email ?? '')));
        if ($accountEmail !== '') {
            return $accountEmail;
        }

        return '';
    }

    private function resolvePhoneDestination(string $loginDestination): ?string
    {
        return $this->normalizeIndianMobile($loginDestination);
    }

    private function normalizeIndianMobile(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        return strlen($digits) === 10 ? $digits : null;
    }
}
