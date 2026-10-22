<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class UserAuthService
{
    public function __construct(private OtpDeliveryService $otpDelivery) {}

    public function findByLogin(string $login): ?User
    {
        $login = $this->normalizeLoginIdentifier($login);

        $digits = preg_replace('/\D+/', '', $login) ?? '';
        $last10 = strlen($digits) >= 10 ? substr($digits, -10) : null;

        return User::query()
            ->where(function ($query) use ($login, $last10) {
                $query->where('email', $login)->orWhere('phone', $login);
                if ($last10) {
                    $query->orWhere('phone', $last10)
                        ->orWhere('phone', '91'.$last10)
                        ->orWhere('phone', '+91'.$last10);
                }
            })
            ->first();
    }

    public function normalizeLoginIdentifier(string $login): string
    {
        $login = trim($login);

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return strtolower($login);
        }

        return (string) $this->normalizePhone($login);
    }

    public function detectLoginChannel(string $login): string
    {
        $login = trim($login);

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (preg_match('/^[0-9+\-\s()]{8,20}$/', $login)) {
            return 'phone';
        }

        throw ValidationException::withMessages([
            'login' => ['Valid email ya phone number enter karein.'],
        ]);
    }

    public function issueOtp(User $user, string $purpose): string
    {
        $otp = (string) random_int(100000, 999999);

        $user->forceFill([
            'otp' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10),
        ])->save();

        return $otp;
    }

    public function sendLoginOtp(string $login): array
    {
        return $this->sendOtp($login, 'login');
    }

    public function verifyLoginOtp(string $login, string $otp, Request $request, string $source): User
    {
        $user = $this->findByLogin($this->normalizeLoginIdentifier($login));

        if (! $user) {
            throw ValidationException::withMessages([
                'otp' => ['Galat OTP. Dobara try karein.'],
            ]);
        }

        $this->assertValidOtp($user, $otp);

        $user->forceFill(['otp' => null, 'otp_expires_at' => null])->save();
        $this->ensureActive($user);
        $this->applyDeviceMeta($user, $request, $source === 'app');
        $this->recordLogin($user, $request, $source);

        return $user->fresh();
    }

    public function sendResetOtp(string $login): array
    {
        return $this->sendOtp($login, 'reset');
    }

    public function sendOtp(string $login, string $purpose): array
    {
        $channel = $this->detectLoginChannel($login);
        $normalized = $this->normalizeLoginIdentifier($login);
        $user = $this->findByLogin($normalized);

        if (! $user) {
            return [
                'user' => null,
                'channel' => $channel,
                'login' => $normalized,
                'otp' => null,
            ];
        }

        $this->ensureActive($user);

        $otp = $this->issueOtp($user, $purpose);
        $deliveredOn = $this->otpDelivery->send($user, $otp, $channel, $purpose);

        return [
            'user' => $user,
            'channel' => $deliveredOn,
            'login' => $normalized,
            'otp' => null,
        ];
    }

    public function resetPasswordWithOtp(string $login, string $otp, string $password): User
    {
        $user = $this->findByLogin($this->normalizeLoginIdentifier($login));

        if (! $user) {
            throw ValidationException::withMessages([
                'otp' => ['Galat OTP. Dobara try karein.'],
            ]);
        }

        $this->assertValidOtp($user, $otp);

        $user->forceFill([
            'password' => $password,
            'otp' => null,
            'otp_expires_at' => null,
        ])->save();

        return $user->fresh();
    }

    private function assertValidOtp(User $user, string $otp): void
    {
        if (! $user->otp || ! $user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            throw ValidationException::withMessages([
                'otp' => ['OTP expire ho gaya hai. Naya OTP mangwayein.'],
            ]);
        }

        $stored = (string) $user->otp;
        $valid = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$2b$')
            ? Hash::check(trim($otp), $stored)
            : hash_equals($stored, trim($otp));

        if (! $valid) {
            throw ValidationException::withMessages([
                'otp' => ['Galat OTP. Dobara try karein.'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data, string $source, Request $request): User
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'] ?? null,
            'google_id' => $data['google_id'] ?? null,
            'profile_image' => $data['profile_image'] ?? null,
            'email_verified_at' => $data['email_verified_at'] ?? null,
            'status' => 1,
            'registration_source' => $source,
        ]);

        $this->applyDeviceMeta($user, $request, $source === 'app');
        $this->recordLogin($user, $request, $source);

        return $user->fresh();
    }

    public function attemptPasswordLogin(string $login, string $password, Request $request, string $source): User
    {
        $user = $this->findByLogin($this->normalizeLoginIdentifier($login));

        if (! $user || ! $user->password || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Invalid email/phone or password.'],
            ]);
        }

        $this->ensureActive($user);
        $this->applyDeviceMeta($user, $request, $source === 'app');
        $this->recordLogin($user, $request, $source);

        return $user->fresh();
    }

    /**
     * @param  array<string, mixed>  $googleProfile
     */
    public function resolveGoogleUser(array $googleProfile, Request $request, string $source): array
    {
        $googleId = (string) ($googleProfile['sub'] ?? $googleProfile['id'] ?? '');
        $email = $googleProfile['email'] ?? null;
        $name = $googleProfile['name'] ?? 'Google User';
        $avatar = $googleProfile['picture'] ?? null;

        if ($googleId === '') {
            throw ValidationException::withMessages([
                'google' => ['Invalid Google account data.'],
            ]);
        }

        $user = User::query()->where('google_id', $googleId)->first();

        if (! $user && $email) {
            $user = User::query()->where('email', $email)->first();
            if ($user && ! $user->google_id) {
                $user->update(['google_id' => $googleId]);
            }
        }

        if (! $user) {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'profile_image' => $avatar,
                'email_verified_at' => $email ? now() : null,
                'status' => 1,
                'registration_source' => $source === 'app' ? 'google_app' : 'google_web',
            ]);
        } else {
            $user->update([
                'name' => $user->name ?: $name,
                'profile_image' => $avatar ?: $user->profile_image,
                'email_verified_at' => $email ? ($user->email_verified_at ?? now()) : $user->email_verified_at,
            ]);
        }

        $needsPhone = empty($user->phone);

        if (! $needsPhone) {
            $this->applyDeviceMeta($user, $request, $source === 'app');
            $this->recordLogin($user, $request, $source === 'app' ? 'app' : 'web');
        }

        return [
            'user' => $user->fresh(),
            'needs_phone' => $needsPhone,
        ];
    }

    public function completeGooglePhone(User $user, string $phone, Request $request, string $source): User
    {
        if (User::query()->where('phone', $phone)->where('id', '!=', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already registered.'],
            ]);
        }

        $user->update(['phone' => $phone]);

        $this->applyDeviceMeta($user, $request, $source === 'app');
        $this->recordLogin($user, $request, $source === 'app' ? 'app' : 'web');

        return $user->fresh();
    }

    public function verifyGoogleIdToken(string $idToken): ?array
    {
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $response->ok()) {
            return null;
        }

        $payload = $response->json();

        if (($payload['aud'] ?? null) !== config('services.google.client_id')) {
            return null;
        }

        return is_array($payload) ? $payload : null;
    }

    public function recordLogin(User $user, Request $request, string $source): void
    {
        $now = now();

        $user->forceFill([
            'login_count' => (int) $user->login_count + 1,
            'last_login_at' => $now,
            'last_login_ip' => $request->ip(),
            'is_logged_in' => true,
            'last_active_at' => $now,
            'web_last_login_at' => $source === 'web' || str_contains($source, 'google_web') ? $now : $user->web_last_login_at,
        ])->save();
    }

    public function recordLogout(User $user): void
    {
        $user->forceFill([
            'is_logged_in' => false,
            'last_logout_at' => now(),
        ])->save();
    }

    public function applyDeviceMeta(User $user, Request $request, bool $fromApp): void
    {
        $fields = [
            'fcm_token', 'device_type', 'device_id', 'device_name', 'device_brand',
            'device_model', 'os_version', 'app_version', 'app_build_number', 'timezone', 'locale',
        ];

        $updates = [];
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $updates[$field] = $request->input($field);
            }
        }

        if (isset($updates['fcm_token'])) {
            $updates['fcm_token_updated_at'] = now();
        }

        if ($fromApp && ! $user->is_app_installed) {
            $updates['is_app_installed'] = true;
            $updates['app_installed_at'] = now();
            $updates['app_uninstalled_at'] = null;
        }

        if ($updates !== []) {
            $user->forceFill($updates)->save();
        }
    }

    public function issueApiToken(User $user, string $deviceName = 'mobile-app'): string
    {
        $user->tokens()->where('name', $deviceName)->delete();

        return $user->createToken($deviceName)->plainTextToken;
    }

    public function webLogin(User $user): void
    {
        Auth::guard('web')->login($user, true);
    }

    public function ensureActive(User $user): void
    {
        if ((int) $user->status !== 1) {
            throw ValidationException::withMessages([
                'login' => ['Your account is inactive. Please contact support.'],
            ]);
        }
    }

    public function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        return preg_replace('/\s+/', '', trim($phone));
    }
}
