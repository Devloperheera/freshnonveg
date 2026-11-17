<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 60);
            $table->boolean('is_active')->default(false);
            $table->string('environment', 20)->default('sandbox');
            $table->text('key_id')->nullable();
            $table->text('key_secret')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('razorpay_order_id', 60)->nullable()->after('cashfree_payment_id');
            $table->string('razorpay_payment_id', 60)->nullable()->after('razorpay_order_id');
            $table->index('razorpay_order_id');
        });

        // Existing .env Cashfree keys ko DB me move karo taaki live checkout band na ho.
        $cfId = trim((string) config('cashfree.app_id', ''));
        $cfSecret = trim((string) config('cashfree.secret_key', ''));
        $cfEnv = strtoupper((string) config('cashfree.environment', 'SANDBOX')) === 'PRODUCTION' ? 'production' : 'sandbox';
        $now = now();

        DB::table('payment_gateways')->insert([
            [
                'code' => 'cashfree',
                'name' => 'Cashfree',
                'is_active' => $cfId !== '' && $cfSecret !== '',
                'environment' => $cfEnv,
                'key_id' => $cfId !== '' ? Crypt::encryptString($cfId) : null,
                'key_secret' => $cfSecret !== '' ? Crypt::encryptString($cfSecret) : null,
                'webhook_secret' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'razorpay',
                'name' => 'Razorpay',
                'is_active' => false,
                'environment' => 'sandbox',
                'key_id' => null,
                'key_secret' => null,
                'webhook_secret' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['razorpay_order_id']);
            $table->dropColumn(['razorpay_order_id', 'razorpay_payment_id']);
        });

        Schema::dropIfExists('payment_gateways');
    }
};
