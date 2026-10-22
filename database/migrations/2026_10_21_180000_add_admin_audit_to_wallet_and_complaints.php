<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('order_id')->constrained('admins')->nullOnDelete();
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->timestamp('reply_emailed_at')->nullable()->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn('reply_emailed_at');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });
    }
};
