<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FcmPushService
{
    private ?string $cachedToken = null;

    private int $tokenExpiresAt = 0;

    public function isConfigured(): bool
    {
        $path = (string) config('firebase.credentials');

        return is_readable($path);
    }

    public function projectId(): ?string
    {
        $configured = config('firebase.project_id');
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $path = (string) config('firebase.credentials');
        if (! is_readable($path)) {
            return null;
        }

        $json = json_decode((string) file_get_contents($path), true);

        return is_array($json) ? ($json['project_id'] ?? null) : null;
    }

    /**
     * @return array{ok: bool, error: ?string}
     */
    public function sendToToken(string $token, string $title, string $body, ?string $imageUrl = null, ?string $clickAction = null): array
    {
        $projectId = $this->projectId();
        if ($projectId === null || $projectId === '') {
            return ['ok' => false, 'error' => 'Firebase credentials / project_id configure karein.'];
        }

        try {
            $accessToken = $this->accessToken();
        } catch (RuntimeException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        $message = [
            'token' => $token,
            'notification' => array_filter([
                'title' => $title,
                'body' => $body,
                'image' => $imageUrl,
            ]),
        ];

        $data = array_filter([
            'click_action' => $clickAction,
            'title' => $title,
            'body' => $body,
        ]);
        if ($data !== []) {
            $message['data'] = collect($data)->map(fn ($v) => (string) $v)->all();
        }

        if ($imageUrl) {
            $message['android'] = ['notification' => ['image' => $imageUrl]];
            $message['apns'] = [
                'payload' => ['aps' => ['mutable-content' => 1]],
                'fcm_options' => ['image' => $imageUrl],
            ];
        }

        $response = Http::withToken($accessToken)
            ->timeout(20)
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => $message,
            ]);

        if ($response->successful()) {
            return ['ok' => true, 'error' => null];
        }

        $error = $response->json('error.message') ?? $response->body();
        Log::warning('FCM send failed', ['token' => substr($token, 0, 12).'…', 'error' => $error]);

        return ['ok' => false, 'error' => is_string($error) ? $error : 'FCM error'];
    }

    private function accessToken(): string
    {
        if ($this->cachedToken && $this->tokenExpiresAt > time() + 60) {
            return $this->cachedToken;
        }

        $path = (string) config('firebase.credentials');
        if (! is_readable($path)) {
            throw new RuntimeException('firebase-service-account.json readable nahi hai.');
        }

        /** @var array<string, mixed> $account */
        $account = json_decode((string) file_get_contents($path), true);
        $email = (string) ($account['client_email'] ?? '');
        $privateKey = (string) ($account['private_key'] ?? '');
        if ($email === '' || $privateKey === '') {
            throw new RuntimeException('Service account JSON incomplete hai.');
        }

        $now = time();
        $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = $this->base64Url(json_encode([
            'iss' => $email,
            'sub' => $email,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ], JSON_THROW_ON_ERROR));
        $unsigned = $header.'.'.$claims;

        $signature = '';
        $key = openssl_pkey_get_private($privateKey);
        if ($key === false) {
            throw new RuntimeException('Firebase private key invalid hai.');
        }
        openssl_sign($unsigned, $signature, $key, OPENSSL_ALGO_SHA256);
        $jwt = $unsigned.'.'.$this->base64Url($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Firebase OAuth token nahi mila.');
        }

        $token = (string) $response->json('access_token');
        if ($token === '') {
            throw new RuntimeException('Firebase access token empty.');
        }

        $this->cachedToken = $token;
        $this->tokenExpiresAt = $now + (int) ($response->json('expires_in') ?? 3500);

        return $token;
    }

    private function base64Url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
