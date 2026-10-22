<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('user_id');
            $table->text('delivery_address')->nullable()->after('phone');
            $table->string('payment_method', 30)->default('cod')->after('delivery_address');
            $table->text('customer_notes')->nullable()->after('payment_method');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            $table->string('weight_label', 80)->nullable()->after('product_name');
            $table->text('preference_note')->nullable()->after('weight_label');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn(['weight_label', 'preference_note']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['phone', 'delivery_address', 'payment_method', 'customer_notes']);
        });
    }
};
