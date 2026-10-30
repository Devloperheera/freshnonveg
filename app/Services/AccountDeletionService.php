<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountDeletionService
{
    public function __construct(private CheckoutService $checkout) {}

    public function assertCanDelete(User $user): void
    {
        if ((float) $user->wallet_balance > 0) {
            throw ValidationException::withMessages([
                'account' => ['Wallet mein balance hai. Pehle order mein use karein ya support se contact karein, phir account delete karein.'],
            ]);
        }

        if ($user->complaints()->whereIn('status', ['open', 'in_progress'])->exists()) {
            throw ValidationException::withMessages([
                'account' => ['Open complaint pending hai. Pehle complaint resolve hone ka wait karein.'],
            ]);
        }
    }

    public function deleteAccount(User $user): void
    {
        $this->assertCanDelete($user);

        DB::transaction(function () use ($user): void {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            $this->checkout->releaseAbandonedOrders((int) $locked->id);

            Order::query()
                ->where('user_id', $locked->id)
                ->where('status', 'awaiting_payment')
                ->each(fn (Order $order) => $this->checkout->failOrder($order));

            $locked->tokens()->delete();
            $locked->cartItems()->delete();
            $locked->wishlistItems()->delete();

            $deletedPhone = 'deleted_'.$locked->id;

            $locked->forceFill([
                'name' => 'Deleted User',
                'email' => null,
                'phone' => $deletedPhone,
                'address' => null,
                'profile_image' => null,
                'latitude' => null,
                'longitude' => null,
                'google_id' => null,
                'password' => null,
                'otp' => null,
                'otp_expires_at' => null,
                'fcm_token' => null,
                'fcm_token_updated_at' => null,
                'device_id' => null,
                'wallet_balance' => 0,
                'status' => 0,
                'is_logged_in' => false,
                'last_logout_at' => now(),
                'notifications_enabled' => false,
            ])->save();
        });
    }
}
