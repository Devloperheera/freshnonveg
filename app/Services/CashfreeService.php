<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class CashfreeService
{
    /**
     * @return array<string, mixed>
     */
    public function createPaymentSession(Order $order, User $user, bool $forMobileApp = false): array
    {
        $returnBase = $this->httpsUrl(route('checkout.cashfree.return', [], true));
        $returnUrl = $forMobileApp
            ? $returnBase.'?source=app&order_id={order_id}'
            : $returnBase.'?order_id={order_id}';

        $payload = [
            'order_id' => $order->order_number,
            'order_amount' => round((float) $order->total, 2),
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id' => 'user_'.$user->id,
                'customer_phone' => $this->customerPhone((string) $order->phone),
                'customer_email' => $user->email ?: 'user'.$user->id.'@freshnonveg.local',
                'customer_name' => $user->name ?: 'Customer',
            ],
            'order_meta' => [
                'return_url' => $returnUrl,
                'notify_url' => $this->httpsUrl(route('checkout.cashfree.webhook', [], true)),
            ],
        ];

        $response = Http::withHeaders($this->headers())
            ->timeout(30)
            ->post($this->apiBase().'/pg/orders', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Cashfree: '.$response->body());
        }

        /** @var array<string, mixed> $data */
        $data = $response->json();

        if (empty($data['payment_session_id']) && empty($data['payment_link'])) {
            throw new RuntimeException('Cashfree: payment_session_id missing.');
        }

        return $data;
    }

    /**
     * App / API ke liye browser/WebView me khulne wala URL (Hamari site + Cashfree JS SDK).
     * Cashfree ka direct `/pg/view/sessions/checkout` URL 404 deta hai — use mat karo.
     */
    public function hostedPayUrl(Order $order, bool $forMobileApp = false): string
    {
        $params = ['order_number' => $order->order_number];
        if ($forMobileApp) {
            $params['app'] = '1';
        }

        return URL::temporarySignedRoute(
            'cashfree.hosted.show',
            now()->addHours(6),
            $params,
            absolute: true
        );
    }

    /**
     * @param  array<string, mixed>  $session
     */
    public function checkoutRedirectUrl(Order $order, array $session, bool $forMobileApp = false): string
    {
        $paymentLink = trim((string) ($session['payment_link'] ?? ''));
        if ($paymentLink !== '' && str_starts_with($paymentLink, 'https://')) {
            return $paymentLink;
        }

        return $this->hostedPayUrl($order, $forMobileApp);
    }

    public function persistPaymentSession(Order $order, array $session): void
    {
        $sessionId = trim((string) ($session['payment_session_id'] ?? ''));
        if ($sessionId === '') {
            return;
        }

        $order->update(['cashfree_payment_session_id' => $sessionId]);
    }

    public function checkoutMode(): string
    {
        return $this->isProduction() ? 'production' : 'sandbox';
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchOrder(string $merchantOrderId): array
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(30)
            ->get($this->apiBase().'/pg/orders/'.rawurlencode($merchantOrderId));

        if (! $response->successful()) {
            throw new RuntimeException('Cashfree fetch: '.$response->body());
        }

        /** @var array<string, mixed> */
        return $response->json();
    }

    /**
     * @param  array<string, mixed>  $cashfreeOrder
     */
    public function isPaid(array $cashfreeOrder): bool
    {
        $status = strtoupper((string) ($cashfreeOrder['order_status'] ?? ''));

        return $status === 'PAID';
    }

    /**
     * @param  array<string, mixed>  $cashfreeOrder
     */
    public function extractPaymentId(array $cashfreeOrder): ?string
    {
        if (! empty($cashfreeOrder['cf_order_id'])) {
            return (string) $cashfreeOrder['cf_order_id'];
        }

        return null;
    }

    public function webhookIsAuthentic(Request $request): bool
    {
        $secret = $this->secretKey();
        $timestamp = (string) $request->header('x-webhook-timestamp', '');
        $signature = (string) $request->header('x-webhook-signature', '');

        if ($secret === '' || $timestamp === '' || $signature === '') {
            return ! app()->environment('production');
        }

        $expected = base64_encode(hash_hmac('sha256', $timestamp.$request->getContent(), $secret, true));

        return hash_equals($expected, $signature);
    }

    public function syncLocalOrderPayment(Order $order): bool
    {
        $remote = $this->fetchOrder($order->order_number);

        if (! $this->isPaid($remote)) {
            return false;
        }

        $alreadyPaid = $order->status === 'paid';

        if ($order->status === 'payment_failed' && (float) $order->wallet_amount > 0 && $order->user) {
            try {
                app(WalletService::class)->debit(
                    $order->user,
                    (float) $order->wallet_amount,
                    'Order '.$order->order_number.' late payment — wallet adjust',
                    $order
                );
            } catch (RuntimeException $e) {
                Log::warning('Wallet re-debit failed for late payment', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            }
        }

        $order->update([
            'status' => 'paid',
            'cashfree_payment_id' => $this->extractPaymentId($remote),
        ]);

        if (! $alreadyPaid || ! $order->bill_emailed_at) {
            $order->refresh();
            app(OrderBillNotifier::class)->sendIfPaid($order);
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'x-client-id' => $this->appId(),
            'x-client-secret' => $this->secretKey(),
            'x-api-version' => (string) config('cashfree.api_version'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    private function apiBase(): string
    {
        return $this->isProduction() ? 'https://api.cashfree.com' : 'https://api-sandbox.cashfree.com';
    }

    public function isConfigured(): bool
    {
        return $this->appId() !== '' && $this->secretKey() !== '';
    }

    public function isProduction(): bool
    {
        $gateway = PaymentGateway::forCode(PaymentGateway::CASHFREE);
        if ($gateway && $gateway->isConfigured()) {
            return $gateway->isProduction();
        }

        return strtoupper((string) config('cashfree.environment')) === 'PRODUCTION';
    }

    public function environmentLabel(): string
    {
        return $this->isProduction() ? 'PRODUCTION' : 'SANDBOX';
    }

    /**
     * Admin "Test connection" — keys sahi hain ya nahi.
     *
     * @return array{ok: bool, message: string}
     */
    public function testCredentials(string $appId, string $secret, bool $production): array
    {
        try {
            $response = Http::withHeaders([
                'x-client-id' => $appId,
                'x-client-secret' => $secret,
                'x-api-version' => (string) config('cashfree.api_version', '2023-08-01'),
                'Accept' => 'application/json',
            ])->timeout(20)->get(
                ($production ? 'https://api.cashfree.com' : 'https://api-sandbox.cashfree.com').'/pg/orders/fnv-connection-test-'.time()
            );
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Cashfree se connect nahi ho paya: '.$e->getMessage()];
        }

        if (in_array($response->status(), [401, 403], true)) {
            return ['ok' => false, 'message' => 'Cashfree ne keys reject ki ('.$response->status().'). App ID / Secret aur environment check karein.'];
        }

        if ($response->status() === 404 || $response->successful()) {
            return ['ok' => true, 'message' => 'Cashfree keys sahi hain ✔'];
        }

        return ['ok' => false, 'message' => 'Cashfree response '.$response->status().': '.mb_substr($response->body(), 0, 200)];
    }

    public function appId(): string
    {
        $gateway = PaymentGateway::forCode(PaymentGateway::CASHFREE);

        return trim((string) ($gateway && $gateway->isConfigured() ? $gateway->key_id : config('cashfree.app_id')));
    }

    private function secretKey(): string
    {
        $gateway = PaymentGateway::forCode(PaymentGateway::CASHFREE);

        return trim((string) ($gateway && $gateway->isConfigured() ? $gateway->key_secret : config('cashfree.secret_key')));
    }

    private function httpsUrl(string $url): string
    {
        if (str_starts_with($url, 'https://')) {
            return $url;
        }

        return 'https://'.preg_replace('#^https?://#', '', $url);
    }

    private function customerPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        return strlen($digits) === 10 ? $digits : '9999999999';
    }
}
