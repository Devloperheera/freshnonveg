<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function credit(
        User $user,
        float $amount,
        string $description,
        ?Complaint $complaint = null,
        ?Order $order = null,
        string $type = 'credit',
        ?int $adminId = null,
    ): WalletTransaction {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new RuntimeException('Credit amount 0 se zyada hona chahiye.');
        }

        return DB::transaction(function () use ($user, $amount, $description, $complaint, $order, $type, $adminId) {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $balance = round((float) $locked->wallet_balance + $amount, 2);
            $locked->forceFill(['wallet_balance' => $balance])->save();

            return WalletTransaction::query()->create([
                'user_id' => $locked->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $balance,
                'description' => $description,
                'complaint_id' => $complaint?->id,
                'order_id' => $order?->id,
                'admin_id' => $adminId,
            ]);
        });
    }

    public function debit(User $user, float $amount, string $description, ?Order $order = null): WalletTransaction
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new RuntimeException('Debit amount 0 se zyada hona chahiye.');
        }

        return DB::transaction(function () use ($user, $amount, $description, $order) {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ((float) $locked->wallet_balance + 0.001 < $amount) {
                throw new RuntimeException('Wallet balance kam hai.');
            }

            $balance = round((float) $locked->wallet_balance - $amount, 2);
            $locked->forceFill(['wallet_balance' => $balance])->save();

            return WalletTransaction::query()->create([
                'user_id' => $locked->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_after' => $balance,
                'description' => $description,
                'order_id' => $order?->id,
            ]);
        });
    }
}
