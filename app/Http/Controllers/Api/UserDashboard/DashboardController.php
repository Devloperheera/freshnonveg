<?php

namespace App\Http\Controllers\Api\UserDashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\CustomerAccountService;
use App\Support\UserDashboardApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private CustomerAccountService $account) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->refresh();

        $recentOrders = $user->orders()->with('items.product.images')->latest('id')->limit(5)->get();
        $recentTransactions = $user->walletTransactions()->latest('id')->limit(4)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => UserDashboardApi::user($user),
                'stats' => $this->account->stats($user),
                'recent_orders' => $recentOrders->map(fn (Order $order) => UserDashboardApi::orderSummary($order))->values()->all(),
                'recent_transactions' => $recentTransactions->map(fn (WalletTransaction $txn) => UserDashboardApi::transaction($txn))->values()->all(),
            ],
        ]);
    }
}
