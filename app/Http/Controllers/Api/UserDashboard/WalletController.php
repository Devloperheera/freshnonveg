<?php

namespace App\Http\Controllers\Api\UserDashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Support\UserDashboardApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->refresh();

        $paginator = $user->walletTransactions()
            ->latest('id')
            ->paginate(min(max((int) $request->input('per_page', 15), 1), 50));

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (float) $user->wallet_balance,
                'total_credited' => (float) $user->walletTransactions()->whereIn('type', ['credit', 'refund'])->sum('amount'),
                'total_used' => (float) $user->walletTransactions()->where('type', 'debit')->sum('amount'),
                'transactions' => collect($paginator->items())
                    ->map(fn (WalletTransaction $txn) => UserDashboardApi::transaction($txn))
                    ->values()
                    ->all(),
            ],
            'meta' => UserDashboardApi::meta($paginator),
        ]);
    }
}
