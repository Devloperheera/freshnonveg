<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20);
            $table->text('description')->nullable();
            $table->decimal('center_lat', 10, 7)->nullable();
            $table->decimal('center_lng', 10, 7)->nullable();
            $table->decimal('radius_km', 8, 2)->nullable();
            $table->string('center_address')->nullable();
            $table->json('polygon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });

        Schema::create('delivery_zone_pincodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_zone_id')->constrained()->cascadeOnDelete();
            $table->string('pincode', 6);
            $table->string('area_name')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->timestamps();

            $table->unique(['delivery_zone_id', 'pincode']);
            $table->index('pincode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zone_pincodes');
        Schema::dropIfExists('delivery_zones');
    }
};
