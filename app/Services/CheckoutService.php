<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Support\CheckoutDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        private CashfreeService $cashfree,
        private RazorpayService $razorpay,
        private WalletService $wallet,
        private DeliveryZoneChecker $zones,
    ) {}

    /**
     * Admin ne jo gateway active kiya hai (keys ke saath). Null = koi online payment ready nahi.
     */
    public function activeGateway(): ?string
    {
        $active = PaymentGateway::active();
        if ($active) {
            return $active->code;
        }

        // Purana setup: payment_gateways table / row nahi hai lekin .env me Cashfree keys hain.
        if (! PaymentGateway::forCode(PaymentGateway::CASHFREE)
            && filled(config('cashfree.app_id'))
            && filled(config('cashfree.secret_key'))) {
            return PaymentGateway::CASHFREE;
        }

        return null;
    }

    public function paymentConfigured(): bool
    {
        return $this->activeGateway() !== null;
    }

    public static function isOnlineGateway(?string $method): bool
    {
        return in_array($method, PaymentGateway::CODES, true);
    }

    /**
     * Order jis gateway se bana tha usi se status sync karo.
     */
    public function syncOrderPayment(Order $order): bool
    {
        return match ($order->payment_method) {
            PaymentGateway::RAZORPAY => $this->razorpay->syncLocalOrderPayment($order),
            PaymentGateway::CASHFREE => $this->cashfree->syncLocalOrderPayment($order),
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function placeRules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'pincode' => ['required', 'regex:/^[0-9]{6}$/'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'post_office' => ['nullable', 'string', 'max:150'],
            'delivery_address' => ['required', 'string', 'max:2000'],
            'delivery_schedule_type' => ['required', 'in:instant,scheduled'],
            'delivery_date' => ['nullable', 'required_if:delivery_schedule_type,scheduled', 'date', 'after_or_equal:today'],
            'delivery_time' => [
                'nullable',
                'required_if:delivery_schedule_type,scheduled',
                'string',
                'max:50',
                Rule::in(array_keys(CheckoutDelivery::timeSlots())),
            ],
            'payment_method' => ['nullable', 'in:cashfree,razorpay,online'],
            'coupon_code' => ['nullable', 'string', 'max:40'],
            'use_wallet' => ['nullable', 'boolean'],
            'order_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function cartItems(User $user): Collection
    {
        return $user->cartItems()->with(['product.images'])->latest('id')->get();
    }

    public function syncPendingPaid(User $user): ?Order
    {
        $pending = Order::query()
            ->where('user_id', $user->id)
            ->whereIn('payment_method', PaymentGateway::CODES)
            ->where('status', 'awaiting_payment')
            ->latest('id')
            ->first();

        if (! $pending) {
            return null;
        }

        try {
            if ($this->syncOrderPayment($pending)) {
                return $pending->fresh(['items.product.images', 'user']);
            }
        } catch (RuntimeException) {
            //
        }

        return null;
    }

    /**
     * @param  array{delivery_schedule_type?: string, coupon_code?: string|null, use_wallet?: bool}  $options
     * @return array<string, mixed>
     */
    public function quote(User $user, array $options = []): array
    {
        $items = $this->cartItems($user);
        $subtotal = round((float) $items->sum(fn (CartItem $item) => $item->lineTotal()), 2);
        $schedule = (string) ($options['delivery_schedule_type'] ?? 'scheduled');
        if (! in_array($schedule, ['instant', 'scheduled'], true)) {
            $schedule = 'scheduled';
        }

        $deliveryFee = CheckoutDelivery::feeForType($schedule);
        $tax = 0.0;
        $shipping = 0.0;

        [$coupon, $discount] = $this->resolveCoupon($options['coupon_code'] ?? null, $subtotal, false);

        $payable = round($subtotal - $discount + $tax + $deliveryFee, 2);
        $walletBalance = (float) $user->wallet_balance;
        $useWallet = (bool) ($options['use_wallet'] ?? false);
        $walletUsed = 0.0;
        if ($useWallet && $payable > 1) {
            $walletUsed = round(max(0, min($walletBalance, $payable - 1)), 2);
        }

        $total = round(max(0, $payable - $walletUsed), 2);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'shipping_label' => $shipping > 0 ? $shipping : 'Free',
            'delivery_schedule_type' => $schedule,
            'delivery_fee' => $deliveryFee,
            'instant_delivery_fee' => CheckoutDelivery::instantFee(),
            'discount' => $discount,
            'coupon' => $coupon,
            'wallet_balance' => $walletBalance,
            'use_wallet' => $useWallet,
            'wallet_used' => $walletUsed,
            'wallet_max_usable' => round(max(0, min($walletBalance, max(0, $payable - 1))), 2),
            'payable' => $payable,
            'total' => $total,
            'minimum_online_pay' => 1.0,
            'time_slots' => CheckoutDelivery::timeSlots(),
            'payment_method' => $this->activeGateway() ?? PaymentGateway::CASHFREE,
            'payment_gateway' => $this->activeGateway(),
            'payment_gateway_label' => PaymentGateway::label($this->activeGateway()),
            'payment_configured' => $this->paymentConfigured(),
            'is_empty' => $items->isEmpty(),
        ];
    }

    /**
     * @return array{0: ?Coupon, 1: float}
     */
    public function resolveCoupon(?string $code, float $subtotal, bool $throwIfInvalid = true): array
    {
        $code = Coupon::normalizeCode($code);
        if ($code === '') {
            return [null, 0.0];
        }

        $coupon = Coupon::query()->where('code', $code)->first();
        if (! $coupon) {
            if (! $throwIfInvalid) {
                return [null, 0.0];
            }
            throw ValidationException::withMessages([
                'coupon_code' => 'Coupon code galat hai.',
            ]);
        }

        $reason = $coupon->rejectionReason($subtotal);
        if ($reason) {
            if (! $throwIfInvalid) {
                return [null, 0.0];
            }
            throw ValidationException::withMessages([
                'coupon_code' => $reason,
            ]);
        }

        return [$coupon, $coupon->discountFor($subtotal)];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function placeOrder(User $user, array $data): Order
    {
        $items = $user->cartItems()->latest('id')->get();
        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Pehle cart mein items add karein.',
            ]);
        }

        $zoneCheck = $this->zones->checkPincode((string) $data['pincode']);
        if ($zoneCheck['deliverable'] === false) {
            throw ValidationException::withMessages([
                'pincode' => $zoneCheck['message'],
            ]);
        }

        $this->releaseAbandonedOrders($user->id);

        $gateway = $this->activeGateway();
        if ($gateway === null) {
            throw ValidationException::withMessages([
                'payment_method' => 'Online payment configure nahi hai. Admin panel se payment gateway (Cashfree / Razorpay) active karein.',
            ]);
        }
        $data['payment_method'] = $gateway;

        $subtotal = $items->sum(fn (CartItem $item) => $item->lineTotal());
        $deliveryFee = CheckoutDelivery::feeForType($data['delivery_schedule_type']);
        [$coupon, $discount] = $this->resolveCoupon($data['coupon_code'] ?? null, $subtotal);
        $tax = 0.0;
        $total = round($subtotal - $discount + $tax + $deliveryFee, 2);

        if ($total < 1) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Discount ke baad minimum ₹1 pay karna hoga.',
            ]);
        }

        $fullAddress = CheckoutDelivery::composeFullAddress($data);
        $useWallet = (bool) ($data['use_wallet'] ?? false);

        return DB::transaction(function () use ($user, $items, $data, $subtotal, $deliveryFee, $discount, $fullAddress, $coupon, $useWallet) {
            if ($coupon) {
                $locked = Coupon::query()->whereKey($coupon->id)->lockForUpdate()->first();
                $reason = $locked?->rejectionReason($subtotal);
                if (! $locked || $reason) {
                    throw ValidationException::withMessages([
                        'coupon_code' => $reason ?? 'Coupon valid nahi hai.',
                    ]);
                }
                $discount = $locked->discountFor($subtotal);
                $locked->increment('used_count');
                $coupon = $locked;
            }

            $payable = round($subtotal - $discount + $deliveryFee, 2);
            $walletUsed = 0.0;
            if ($useWallet) {
                $balance = (float) User::query()->whereKey($user->id)->lockForUpdate()->value('wallet_balance');
                $walletUsed = round(max(0, min($balance, $payable - 1)), 2);
            }

            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'phone' => $data['phone'],
                'delivery_address' => $fullAddress,
                'pincode' => $data['pincode'],
                'state' => $data['state'],
                'city' => $data['city'],
                'post_office' => $data['post_office'] ?? null,
                'delivery_schedule_type' => $data['delivery_schedule_type'],
                'delivery_date' => $data['delivery_schedule_type'] === 'scheduled' ? $data['delivery_date'] : null,
                'delivery_time' => $data['delivery_schedule_type'] === 'scheduled'
                    ? $data['delivery_time']
                    : 'instant',
                'delivery_fee' => $deliveryFee,
                'payment_method' => $data['payment_method'],
                'customer_notes' => $data['order_notes'] ?? null,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'discount_amount' => $discount,
                'wallet_amount' => $walletUsed,
                'total' => round($payable - $walletUsed, 2),
                'status' => 'awaiting_payment',
            ]);

            if ($walletUsed > 0) {
                $this->wallet->debit($user, $walletUsed, 'Order '.$order->order_number.' me wallet use hua', $order);
            }

            foreach ($items as $cartItem) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product_name,
                    'weight_label' => $cartItem->weight_label,
                    'preference_note' => $cartItem->preference_note,
                    'quantity' => (int) $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                ]);
            }

            if (empty($user->address)) {
                $user->update([
                    'phone' => $data['phone'],
                    'address' => $fullAddress,
                ]);
            }

            return $order;
        });
    }

    /**
     * Order ke gateway par payment start karo (Cashfree session / Razorpay order).
     *
     * @return array<string, mixed>
     */
    public function startPayment(Order $order, User $user, ?Request $request = null): array
    {
        $gateway = (string) $order->payment_method;
        $forMobileApp = \App\Support\ApiClientContext::isMobileApp($request);

        try {
            $session = $gateway === PaymentGateway::RAZORPAY
                ? $this->razorpay->createOrder($order, $user)
                : $this->cashfree->createPaymentSession($order, $user, $forMobileApp);

            if ($gateway === PaymentGateway::CASHFREE) {
                $this->cashfree->persistPaymentSession($order, $session);
            }

            $user->cartItems()->delete();

            return $session;
        } catch (RuntimeException $e) {
            Log::error(PaymentGateway::label($gateway).' checkout failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            $this->failOrder($order);

            $message = $gateway === PaymentGateway::CASHFREE && str_contains($e->getMessage(), '403')
                ? 'Cashfree ne payment block ki (403). Live website ka HTTPS domain Cashfree dashboard me whitelist karein, aur production API keys sahi hon.'
                : ($gateway === PaymentGateway::RAZORPAY && str_contains($e->getMessage(), '401')
                    ? 'Razorpay keys galat hain (401). Admin panel me Razorpay Key ID / Secret check karein.'
                    : 'Payment start nahi ho payi. Cart safe hai, dubara try karein.');

            throw ValidationException::withMessages([
                'payment_method' => $message,
            ]);
        }
    }

    /**
     * @deprecated startPayment() use karein.
     *
     * @return array<string, mixed>
     */
    public function startCashfree(Order $order, User $user): array
    {
        return $this->startPayment($order, $user);
    }

    public function verifyPaid(Order $order): bool
    {
        if ($order->status === 'paid' || $order->status === 'confirmed') {
            return true;
        }

        try {
            return $this->syncOrderPayment($order);
        } catch (RuntimeException $e) {
            Log::warning(PaymentGateway::label($order->payment_method).' verify failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function markFailedIfUnpaid(Order $order): void
    {
        if ($order->status === 'awaiting_payment') {
            $this->failOrder($order);
        }
    }

    public function failOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== 'awaiting_payment') {
                return;
            }

            if ($locked->coupon_id) {
                Coupon::query()
                    ->whereKey($locked->coupon_id)
                    ->where('used_count', '>', 0)
                    ->decrement('used_count');
            }

            if ((float) $locked->wallet_amount > 0 && $locked->user) {
                $this->wallet->credit(
                    $locked->user,
                    (float) $locked->wallet_amount,
                    'Order '.$locked->order_number.' payment fail — wallet refund',
                    null,
                    $locked,
                    'refund'
                );
            }

            $locked->update(['status' => 'payment_failed']);
        });

        $order->refresh();
    }

    public function releaseAbandonedOrders(int $userId): void
    {
        $pending = Order::query()
            ->where('user_id', $userId)
            ->whereIn('payment_method', PaymentGateway::CODES)
            ->where('status', 'awaiting_payment')
            ->get();

        foreach ($pending as $order) {
            try {
                if ($this->syncOrderPayment($order)) {
                    continue;
                }
            } catch (RuntimeException) {
                continue;
            }

            $this->failOrder($order);
        }
    }

    /**
     * @param  array<string, mixed>  $session
     * @return array<string, mixed>
     */
    public function paymentPayload(Order $order, array $session, bool $forMobileApp = false): array
    {
        if ($order->payment_method === PaymentGateway::RAZORPAY) {
            $options = $this->razorpay->checkoutOptions($order);

            return array_merge([
                'gateway' => PaymentGateway::RAZORPAY,
                'environment' => $this->razorpay->environmentLabel(),
                'order_id' => $order->order_number,
                'order_amount' => (float) $order->total,
                'order_currency' => 'INR',
                'key_id' => $options['key'],
                'razorpay_order_id' => $order->razorpay_order_id,
                'amount_paise' => $options['amount'],
                'checkout_options' => $options,
                'payment_session_id' => null,
                'cf_order_id' => null,
                'payment_link' => null,
                'checkout_url' => null,
            ], $this->mobileIntegrationHints(PaymentGateway::RAZORPAY, $forMobileApp, null));
        }

        $checkoutUrl = $this->cashfree->checkoutRedirectUrl($order, $session, $forMobileApp);

        return array_merge([
            'gateway' => PaymentGateway::CASHFREE,
            'environment' => $this->cashfree->environmentLabel(),
            'order_id' => $order->order_number,
            'order_amount' => (float) $order->total,
            'order_currency' => 'INR',
            'payment_session_id' => $session['payment_session_id'] ?? null,
            'cf_order_id' => $session['cf_order_id'] ?? null,
            'payment_link' => $session['payment_link'] ?? null,
            'checkout_url' => $checkoutUrl,
        ], $this->mobileIntegrationHints(PaymentGateway::CASHFREE, $forMobileApp, $checkoutUrl));
    }

    /**
     * @return array<string, mixed>
     */
    private function mobileIntegrationHints(string $gateway, bool $forMobileApp, ?string $checkoutUrl): array
    {
        $preferHosted = $forMobileApp && $gateway === PaymentGateway::CASHFREE;

        return [
            'integration' => [
                'client' => $forMobileApp ? 'mobile_app' : 'web',
                'preferred' => $preferHosted ? 'hosted_checkout_url' : 'sdk',
                'use_native_sdk' => ! $preferHosted,
                'open_checkout_in' => $preferHosted ? ['external_browser', 'in_app_webview'] : ['sdk', 'webview'],
                'checkout_url' => $checkoutUrl,
            ],
            'mobile_hints' => $preferHosted ? [
                'title' => 'Cashfree hosted checkout use karein',
                'message' => 'payment.checkout_url (freshnonveg.com/pay/cashfree/...) browser ya WebView me kholo — ye Cashfree JS SDK use karta hai. Payment ke baad POST /checkout/verify call karein.',
            ] : null,
        ];
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'FNV-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
