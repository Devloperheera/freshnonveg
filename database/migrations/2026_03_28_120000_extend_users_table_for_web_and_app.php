<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();

            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->string('profile_image')->nullable()->after('phone');
            $table->decimal('latitude', 10, 7)->nullable()->after('profile_image');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->text('address')->nullable()->after('longitude');
            $table->unsignedTinyInteger('status')->default(1)->after('address');

            $table->string('google_id')->nullable()->unique()->after('status');
            $table->string('otp', 10)->nullable()->after('google_id');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');

            $table->string('registration_source', 20)->nullable()->after('remember_token');
            $table->timestamp('web_last_login_at')->nullable()->after('registration_source');
            $table->timestamp('app_installed_at')->nullable()->after('web_last_login_at');

            $table->text('fcm_token')->nullable()->after('app_installed_at');
            $table->timestamp('fcm_token_updated_at')->nullable()->after('fcm_token');

            $table->string('device_type', 30)->nullable()->after('fcm_token_updated_at');
            $table->string('device_id')->nullable()->after('device_type');
            $table->string('device_name')->nullable()->after('device_id');
            $table->string('device_brand', 80)->nullable()->after('device_name');
            $table->string('device_model', 80)->nullable()->after('device_brand');
            $table->string('os_version', 40)->nullable()->after('device_model');
            $table->string('app_version', 30)->nullable()->after('os_version');
            $table->string('app_build_number', 30)->nullable()->after('app_version');

            $table->string('timezone', 64)->nullable()->after('app_build_number');
            $table->string('locale', 20)->nullable()->after('timezone');

            $table->unsignedInteger('login_count')->default(0)->after('locale');
            $table->timestamp('last_login_at')->nullable()->after('login_count');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->boolean('is_logged_in')->default(false)->after('last_login_ip');
            $table->timestamp('last_active_at')->nullable()->after('is_logged_in');
            $table->timestamp('last_logout_at')->nullable()->after('last_active_at');

            $table->boolean('is_app_installed')->default(false)->after('last_logout_at');
            $table->timestamp('app_uninstalled_at')->nullable()->after('is_app_installed');

            $table->boolean('notifications_enabled')->default(true)->after('app_uninstalled_at');
            $table->timestamp('notifications_disabled_at')->nullable()->after('notifications_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'profile_image', 'latitude', 'longitude', 'address', 'status',
                'google_id', 'otp', 'otp_expires_at', 'registration_source', 'web_last_login_at',
                'app_installed_at', 'fcm_token', 'fcm_token_updated_at', 'device_type', 'device_id',
                'device_name', 'device_brand', 'device_model', 'os_version', 'app_version',
                'app_build_number', 'timezone', 'locale', 'login_count', 'last_login_at',
                'last_login_ip', 'is_logged_in', 'last_active_at', 'last_logout_at',
                'is_app_installed', 'app_uninstalled_at', 'notifications_enabled', 'notifications_disabled_at',
            ]);
        });
    }
};
