<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CashfreeService
{
    /**
     * @return array<string, mixed>
     */
    public function createPaymentSession(Order $order, User $user): array
    {
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
                'return_url' => $this->httpsUrl(route('checkout.cashfree.return', [], true)).'?order_id={order_id}',
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
     * @param  array<string, mixed>  $session
     */
    public function checkoutRedirectUrl(string $orderId, array $session): string
    {
        $paymentLink = trim((string) ($session['payment_link'] ?? ''));
        if ($paymentLink !== '' && str_starts_with($paymentLink, 'https://')) {
            return $paymentLink;
        }

        $paymentSessionId = (string) ($session['payment_session_id'] ?? '');
        $base = rtrim((string) config('cashfree.checkout_base_url'), '/');

        return $base.'/pg/view/sessions/checkout/web/'.rawurlencode($paymentSessionId)
            .'?order_id='.rawurlencode($orderId);
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
        $secret = (string) config('cashfree.secret_key');
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
            'x-client-id' => (string) config('cashfree.app_id'),
            'x-client-secret' => (string) config('cashfree.secret_key'),
            'x-api-version' => (string) config('cashfree.api_version'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    private function apiBase(): string
    {
        return rtrim((string) config('cashfree.api_base_url'), '/');
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
