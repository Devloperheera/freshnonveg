<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Fast2SmsService
{
    public function isConfigured(): bool
    {
        return config('services.sms.driver') === 'fast2sms'
            && filled(config('services.fast2sms.api_key'))
            && filled(config('services.fast2sms.sender_id'));
    }

    /**
     * @return array{ok: bool, error: ?string}
     */
    public function sendDlt(
        string $mobile10,
        string $messageId,
        string $variablesValues,
        ?string $dltTemplateId = null,
    ): array {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'error' => 'Fast2SMS not configured'];
        }

        if (! preg_match('/^\d{10}$/', $mobile10)) {
            return ['ok' => false, 'error' => 'Invalid mobile number'];
        }

        $query = [
            'authorization' => (string) config('services.fast2sms.api_key'),
            'route' => 'dlt',
            'sender_id' => (string) config('services.fast2sms.sender_id'),
            'message' => $messageId,
            'variables_values' => $variablesValues,
            'numbers' => $mobile10,
            'flash' => '0',
        ];

        if (filled($dltTemplateId)) {
            $query['dlt_content_template_id'] = $dltTemplateId;
        }

        try {
            $response = Http::timeout(25)->get('https://www.fast2sms.com/dev/bulkV2', $query);
        } catch (Throwable $e) {
            Log::error('Fast2SMS request failed', ['error' => $e->getMessage(), 'message_id' => $messageId]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }

        $body = $response->json();
        $ok = $response->successful() && (
            ($body['return'] ?? false) === true
            || ($body['status'] ?? '') === 'OK'
        );

        if (! $ok) {
            Log::warning('Fast2SMS rejected message', [
                'message_id' => $messageId,
                'status' => $response->status(),
                'body' => is_array($body) ? $body : $response->body(),
            ]);

            $message = is_array($body) ? (string) ($body['message'] ?? 'SMS rejected') : 'SMS rejected';

            return ['ok' => false, 'error' => $message];
        }

        return ['ok' => true, 'error' => null];
    }

    public function sendOtp(string $mobile10, string $otp): array
    {
        return $this->sendDlt(
            $mobile10,
            (string) config('services.fast2sms.message_id'),
            $otp,
            config('services.fast2sms.dlt_template_id') ? (string) config('services.fast2sms.dlt_template_id') : null,
        );
    }
}
