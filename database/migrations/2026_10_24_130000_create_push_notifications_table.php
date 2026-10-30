<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_group_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('image')->nullable();
            $table->string('click_action', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_summary')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
