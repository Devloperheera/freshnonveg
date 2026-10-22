<?php

namespace Database\Seeders;

use App\Models\DeliverySlot;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoOrderSeeder extends Seeder
{
    private const ORDER_NUMBER = 'FNV-DEMO-0001';

    public function run(): void
    {
        $user = User::query()
            ->where('phone', '9899050881')
            ->orWhere('email', 'heeralalzx2020@gmail.com')
            ->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => 'Heera',
                'phone' => '9899050881',
                'email' => 'heeralalzx2020@gmail.com',
                'status' => 1,
                'address' => 'Nangloi Jat, Delhi',
            ]);
        }

        $products = Product::query()
            ->where('status', 1)
            ->whereHas('weights')
            ->with('weights')
            ->orderBy('id')
            ->limit(3)
            ->get();

        if ($products->isEmpty()) {
            $this->command?->warn('Koi active product nahi mila — pehle ProductSeeder chalayein.');

            return;
        }

        $slot = DeliverySlot::query()->where('status', 1)->where('start_time', '>=', '12:00:00')->orderBy('start_time')->first()
            ?? DeliverySlot::query()->where('status', 1)->orderBy('start_time')->first();

        DB::transaction(function () use ($user, $products, $slot) {
            Order::query()->where('order_number', self::ORDER_NUMBER)->first()?->delete();

            $lines = $products->values()->map(function (Product $product, int $i) {
                $weight = $product->weights->first();

                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'weight_label' => $weight->weight_label,
                    'preference_note' => $i === 0 ? 'Leg piece zyada' : null,
                    'quantity' => $i === 0 ? 2 : 1,
                    'unit_price' => (float) $weight->price,
                ];
            });

            $subtotal = $lines->sum(fn ($l) => $l['unit_price'] * $l['quantity']);

            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_number' => self::ORDER_NUMBER,
                'phone' => $user->phone ?: '9899050881',
                'delivery_address' => 'H.No. 21, Gali No. 4, Nangloi Jat, Delhi - 110041',
                'pincode' => '110041',
                'state' => 'Delhi',
                'city' => 'West Delhi',
                'post_office' => 'Nangloi Jat',
                'delivery_schedule_type' => 'scheduled',
                'delivery_date' => now()->addDay()->toDateString(),
                'delivery_time' => $slot?->slot_key ?? '12:00-13:00',
                'delivery_fee' => 0,
                'payment_method' => 'cashfree',
                'customer_notes' => 'Ghanti bajane se pehle call karein.',
                'discount_amount' => 0,
                'wallet_amount' => 0,
                'cashfree_payment_id' => 'DEMO-PAY-0001',
                'total' => round($subtotal, 2),
                'status' => 'paid',
                'bill_emailed_at' => now(),
            ]);

            $order->items()->createMany($lines->all());
        });

        $this->command?->info('Demo order '.self::ORDER_NUMBER.' bana diya — user: '.$user->name.' ('.$user->phone.')');
    }
}
