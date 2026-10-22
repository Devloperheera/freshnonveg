<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pincode', 6)->nullable()->after('delivery_address');
            $table->string('state', 100)->nullable()->after('pincode');
            $table->string('city', 100)->nullable()->after('state');
            $table->string('post_office', 150)->nullable()->after('city');
            $table->string('delivery_schedule_type', 20)->default('scheduled')->after('post_office');
            $table->date('delivery_date')->nullable()->after('delivery_schedule_type');
            $table->string('delivery_time', 50)->nullable()->after('delivery_date');
            $table->decimal('delivery_fee', 12, 2)->default(0)->after('delivery_time');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'pincode',
                'state',
                'city',
                'post_office',
                'delivery_schedule_type',
                'delivery_date',
                'delivery_time',
                'delivery_fee',
            ]);
        });
    }
};
