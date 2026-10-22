<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'profile_image',
        'latitude',
        'longitude',
        'address',
        'wallet_balance',
        'status',
        'google_id',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'password',
        'registration_source',
        'web_last_login_at',
        'app_installed_at',
        'fcm_token',
        'fcm_token_updated_at',
        'device_type',
        'device_id',
        'device_name',
        'device_brand',
        'device_model',
        'os_version',
        'app_version',
        'app_build_number',
        'timezone',
        'locale',
        'login_count',
        'last_login_at',
        'last_login_ip',
        'is_logged_in',
        'last_active_at',
        'last_logout_at',
        'is_app_installed',
        'app_uninstalled_at',
        'notifications_enabled',
        'notifications_disabled_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'fcm_token',
        'google_id',
        'device_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'web_last_login_at' => 'datetime',
            'app_installed_at' => 'datetime',
            'fcm_token_updated_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_active_at' => 'datetime',
            'last_logout_at' => 'datetime',
            'app_uninstalled_at' => 'datetime',
            'notifications_disabled_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'integer',
            'login_count' => 'integer',
            'is_logged_in' => 'boolean',
            'is_app_installed' => 'boolean',
            'notifications_enabled' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'wallet_balance' => 'decimal:2',
        ];
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }
}
