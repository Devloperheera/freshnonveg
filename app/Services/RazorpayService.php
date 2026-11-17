<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class RazorpayService
{
    private const API = 'https://api.razorpay.com/v1';

    public function gateway(): ?PaymentGateway
    {
        return PaymentGateway::forCode(PaymentGateway::RAZORPAY);
    }

    public function isConfigured(): bool
    {
        return (bool) $this->gateway()?->isConfigured();
    }

    public function keyId(): string
    {
        return trim((string) $this->gateway()?->key_id);
    }

    private function keySecret(): string
    {
        return trim((string) $this->gateway()?->key_secret);
    }

    public function environmentLabel(): string
    {
        return str_starts_with($this->keyId(), 'rzp_live_') ? 'PRODUCTION' : 'SANDBOX';
    }

    /**
     * Razorpay order banao (amount paise me).
     *
     * @return array<string, mixed>
     */
    public function createOrder(Order $order, User $user): array
    {
        $response = $this->client()->post(self::API.'/orders', [
            'amount' => $this->toPaise((float) $order->total),
            'currency' => 'INR',
            'receipt' => mb_substr((string) $order->order_number, 0, 40),
            'payment_capture' => 1,
            'notes' => [
                'order_number' => (string) $order->order_number,
                'user_id' => (string) $user->id,
            ],
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Razorpay: '.$response->status().' '.$response->body());
        }

        /** @var array<string, mixed> $data */
        $data = $response->json();

        if (empty($data['id'])) {
            throw new RuntimeException('Razorpay: order id missing.');
        }

        $order->update(['razorpay_order_id' => (string) $data['id']]);

        return $data;
    }

    /**
     * Razorpay checkout.js / mobile SDK ke liye options.
     *
     * @return array<string, mixed>
     */
    public function checkoutOptions(Order $order, ?User $user = null): array
    {
        $user ??= $order->user;

        return [
            'key' => $this->keyId(),
            'amount' => $this->toPaise((float) $order->total),
            'currency' => 'INR',
            'name' => (string) config('app.name', 'FreshNonVeg'),
            'description' => 'Order '.$order->order_number,
            'order_id' => (string) $order->razorpay_order_id,
            'prefill' => [
                'name' => (string) ($user?->name ?? ''),
                'email' => (string) ($user?->email ?? ''),
                'contact' => $this->customerPhone((string) $order->phone),
            ],
            'notes' => [
                'order_number' => (string) $order->order_number,
            ],
            'theme' => ['color' => '#d62828'],
        ];
    }

    public function signatureIsValid(string $razorpayOrderId, string $paymentId, string $signature): bool
    {
        $secret = $this->keySecret();
        if ($secret === '' || $razorpayOrderId === '' || $paymentId === '' || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $razorpayOrderId.'|'.$paymentId, $secret);

        return hash_equals($expected, $signature);
    }

    public function webhookIsAuthentic(Request $request): bool
    {
        $secret = trim((string) $this->gateway()?->webhook_secret);
        $signature = (string) $request->header('X-Razorpay-Signature', '');

        if ($secret === '' || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Razorpay se payment status check karke local order update karo.
     */
    public function syncLocalOrderPayment(Order $order): bool
    {
        $razorpayOrderId = (string) $order->razorpay_order_id;
        if ($razorpayOrderId === '') {
            return false;
        }

        $payment = $this->findCapturedPayment($razorpayOrderId, (float) $order->total);
        if (! $payment) {
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

        if (! in_array($order->status, ['paid', 'confirmed'], true)) {
            $order->status = 'paid';
        }
        $order->razorpay_payment_id = (string) ($payment['id'] ?? '');
        $order->save();

        if (! $alreadyPaid || ! $order->bill_emailed_at) {
            $order->refresh();
            app(OrderBillNotifier::class)->sendIfPaid($order);
        }

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findCapturedPayment(string $razorpayOrderId, float $expectedAmount): ?array
    {
        $response = $this->client()->get(self::API.'/orders/'.rawurlencode($razorpayOrderId).'/payments');

        if (! $response->successful()) {
            throw new RuntimeException('Razorpay fetch: '.$response->status().' '.$response->body());
        }

        $items = (array) ($response->json('items') ?? []);
        $expectedPaise = $this->toPaise($expectedAmount);

        foreach ($items as $payment) {
            if (($payment['status'] ?? '') === 'captured') {
                return $payment;
            }
        }

        // Auto-capture off ho to authorized payment ko capture karo.
        foreach ($items as $payment) {
            if (($payment['status'] ?? '') !== 'authorized' || empty($payment['id'])) {
                continue;
            }

            $amount = (int) ($payment['amount'] ?? $expectedPaise);
            $capture = $this->client()->post(self::API.'/payments/'.rawurlencode((string) $payment['id']).'/capture', [
                'amount' => $amount,
                'currency' => (string) ($payment['currency'] ?? 'INR'),
            ]);

            if ($capture->successful() && ($capture->json('status') === 'captured')) {
                return (array) $capture->json();
            }

            Log::warning('Razorpay capture failed', ['payment' => $payment['id'], 'body' => $capture->body()]);
        }

        return null;
    }

    /**
     * Admin "Test connection".
     *
     * @return array{ok: bool, message: string}
     */
    public function testCredentials(string $keyId, string $secret): array
    {
        try {
            $response = Http::withBasicAuth($keyId, $secret)
                ->acceptJson()
                ->timeout(20)
                ->get(self::API.'/orders', ['count' => 1]);
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Razorpay se connect nahi ho paya: '.$e->getMessage()];
        }

        if ($response->successful()) {
            return ['ok' => true, 'message' => 'Razorpay keys sahi hain ✔'];
        }

        if ($response->status() === 401) {
            return ['ok' => false, 'message' => 'Razorpay ne keys reject ki (401). Key ID / Key Secret check karein.'];
        }

        return ['ok' => false, 'message' => 'Razorpay response '.$response->status().': '.mb_substr($response->body(), 0, 200)];
    }

    private function client(): PendingRequest
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Razorpay keys configure nahi hain.');
        }

        return Http::withBasicAuth($this->keyId(), $this->keySecret())
            ->acceptJson()
            ->asJson()
            ->timeout(30);
    }

    private function toPaise(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function customerPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        return strlen($digits) === 10 ? $digits : '';
    }
}
