<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CustomerAccountService;
use App\Support\CheckoutDelivery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SmokeTestCustomerFlow extends Command
{
    protected $signature = 'app:smoke-customer {--base=http://127.0.0.1:8000}';

    protected $description = 'Cart → order → dashboard → rating → complaint smoke test';

    /** @var list<array{check: string, status: string, detail: string}> */
    private array $results = [];

    public function handle(
        CartService $cart,
        CheckoutService $checkout,
        CustomerAccountService $account,
    ): int {
        $base = rtrim((string) $this->option('base'), '/');

        $this->line('=== FreshNonVeg customer smoke test ===');
        $this->line('Base URL: '.$base);

        $product = Product::query()->where('status', 1)->with('weights')->first();
        if (! $product || $product->weights->isEmpty()) {
            $this->failCheck('catalog', 'No active product with weights');

            return $this->report(1);
        }

        $user = User::query()->firstOrCreate(
            ['phone' => '9998887770'],
            [
                'name' => 'Smoke Test User',
                'email' => 'smoke-test-'.Str::random(6).'@freshnonveg.test',
                'password' => bcrypt('SmokeTest@123'),
                'wallet_balance' => 0,
                'address' => 'Smoke test address, Sector 110, Noida',
            ]
        );

        Auth::login($user);

        $user->cartItems()->delete();

        try {
            $cart->addToCart($user, $product, [
                'product_weight_id' => (int) $product->weights->first()->id,
                'quantity' => 5,
                'preference_note' => 'Smoke test cut',
            ]);
            $this->passCheck('cart', 'Product added to cart (qty 5)');
        } catch (\Throwable $e) {
            $this->failCheck('cart', $e->getMessage());

            return $this->report(1);
        }

        $quote = $checkout->quote($user);
        if ($quote['is_empty']) {
            $this->failCheck('checkout-quote', 'Cart empty in quote');

            return $this->report(1);
        }
        $this->passCheck('checkout-quote', 'Subtotal ₹'.number_format((float) $quote['subtotal'], 2));

        $slots = CheckoutDelivery::timeSlots();
        $slotKey = array_key_first($slots);

        $order = null;
        if ($quote['payment_configured'] && $slotKey) {
            try {
                $order = $checkout->placeOrder($user, [
                    'phone' => $user->phone ?? '9998887770',
                    'pincode' => '201301',
                    'state' => 'Uttar Pradesh',
                    'city' => 'Noida',
                    'delivery_address' => 'A35, Sector 110, Noida',
                    'delivery_schedule_type' => 'scheduled',
                    'delivery_date' => now()->addDay()->format('Y-m-d'),
                    'delivery_time' => $slotKey,
                    'order_notes' => 'Automated smoke test order',
                ]);
                $this->passCheck('checkout-place', 'Order '.$order->order_number.' created (awaiting payment)');
            } catch (\Throwable $e) {
                $this->failCheck('checkout-place', $e->getMessage());
            }
        } else {
            $reason = ! $quote['payment_configured']
                ? 'Payment gateway not configured'
                : 'No active delivery slot';
            $this->warnCheck('checkout-place', $reason.' — simulating paid order in DB');
            $order = $this->simulatePaidOrder($user, $product, $quote);
            if ($order) {
                $this->passCheck('checkout-simulate', 'Paid order '.$order->order_number.' created for dashboard tests');
            }
        }

        if ($order) {
            DB::transaction(function () use ($order, $user) {
                $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();
                if ($locked && $locked->status === 'awaiting_payment') {
                    $locked->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                    $user->cartItems()->delete();
                }
            });
            $this->passCheck('checkout-paid', 'Order marked paid for rating flow');
        }

        $stats = $account->stats($user->fresh());
        $this->passCheck('dashboard-stats', json_encode($stats));

        try {
            $rating = $account->storeRating($user, $product, [
                'rating' => 5,
                'review' => 'Smoke test rating — fresh product',
            ]);
            if ($rating['ok']) {
                $this->passCheck('rating', '5-star review saved');
            } else {
                $this->warnCheck('rating', $rating['message']);
            }
        } catch (\Throwable $e) {
            $this->failCheck('rating', $e->getMessage());
        }

        $complaint = $account->storeComplaint($user, [
            'order_id' => $order?->id,
            'subject' => 'Smoke test complaint',
            'message' => 'Automated test — please ignore.',
        ]);
        $this->passCheck('complaint', 'Complaint #'.$complaint->id.' registered');

        $paths = [
            'home' => route('home', absolute: false),
            'shop' => route('shop.index', absolute: false),
            'cart' => route('cart', absolute: false),
            'dashboard' => route('dashboard', absolute: false),
            'dashboard-orders' => route('dashboard.orders', absolute: false),
            'dashboard-ratings' => route('dashboard.ratings', absolute: false),
            'dashboard-complaints' => route('dashboard.complaints', absolute: false),
            'admin-settings' => route('admin.settings.index', absolute: false),
        ];

        foreach ($paths as $label => $path) {
            $url = $base.$path;
            try {
                $resp = Http::timeout(25)->get($url);
                $code = $resp->status();
                if ($code >= 200 && $code < 400) {
                    $this->passCheck('http-'.$label, $code.' '.$url);
                } elseif ($code === 302 && in_array($label, ['cart', 'dashboard', 'dashboard-orders', 'dashboard-ratings', 'dashboard-complaints'], true)) {
                    $this->passCheck('http-'.$label, '302 login redirect (guest) '.$url);
                } else {
                    $this->failCheck('http-'.$label, $code.' '.$url);
                }
            } catch (\Throwable $e) {
                $this->failCheck('http-'.$label, $e->getMessage());
            }
        }

        Auth::logout();

        return $this->report();
    }

    private function simulatePaidOrder(User $user, Product $product, array $quote): ?Order
    {
        $weight = $product->weights->first();
        $unit = (float) $weight->price;
        $qty = 5;
        $subtotal = $unit * $qty;

        return DB::transaction(function () use ($user, $product, $weight, $qty, $unit, $subtotal) {
            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_number' => 'SMOKE-'.now()->format('YmdHis'),
                'phone' => $user->phone ?? '9998887770',
                'delivery_address' => 'A35, Sector 110, Noida, Uttar Pradesh, 201301',
                'pincode' => '201301',
                'state' => 'Uttar Pradesh',
                'city' => 'Noida',
                'delivery_schedule_type' => 'scheduled',
                'delivery_date' => now()->addDay()->toDateString(),
                'delivery_time' => 'morning',
                'delivery_fee' => 0,
                'total' => $subtotal,
                'status' => 'paid',
                'payment_method' => 'razorpay',
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'weight_label' => $weight->weight_label,
                'quantity' => $qty,
                'unit_price' => $unit,
            ]);

            return $order;
        });
    }

    private function passCheck(string $check, string $detail): void
    {
        $this->results[] = ['check' => $check, 'status' => 'PASS', 'detail' => $detail];
    }

    private function failCheck(string $check, string $detail): void
    {
        $this->results[] = ['check' => $check, 'status' => 'FAIL', 'detail' => $detail];
    }

    private function warnCheck(string $check, string $detail): void
    {
        $this->results[] = ['check' => $check, 'status' => 'WARN', 'detail' => $detail];
    }

    private function report(int $forcedExit = 0): int
    {
        $this->newLine();
        $this->table(['Check', 'Status', 'Detail'], $this->results);

        $fails = collect($this->results)->where('status', 'FAIL')->count();
        $warns = collect($this->results)->where('status', 'WARN')->count();

        if ($fails > 0) {
            $this->error("Failed: {$fails} check(s), warnings: {$warns}");

            return $forcedExit ?: 1;
        }

        $this->info("All critical checks passed (warnings: {$warns}).");

        return $forcedExit ?: 0;
    }
}