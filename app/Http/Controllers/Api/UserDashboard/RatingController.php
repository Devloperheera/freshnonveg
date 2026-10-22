<?php

namespace App\Http\Controllers\Api\UserDashboard;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CustomerAccountService;
use App\Support\UserDashboardApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function __construct(private CustomerAccountService $account) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $purchased = $this->account->purchasedItems($user);
        $reviews = $user->productReviews()->get()->keyBy('product_id');

        $pending = $purchased
            ->reject(fn (OrderItem $line) => $reviews->has($line->product_id))
            ->map(fn (OrderItem $line) => UserDashboardApi::ratingItem($line, null))
            ->values()
            ->all();

        $rated = $purchased
            ->filter(fn (OrderItem $line) => $reviews->has($line->product_id))
            ->map(fn (OrderItem $line) => UserDashboardApi::ratingItem($line, $reviews->get($line->product_id)))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'pending' => $pending,
                'rated' => $rated,
            ],
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = $this->account->storeRating($user, $product, $data);
        if (! $result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $product->name.' ke liye rating dene ka shukriya!',
            'data' => UserDashboardApi::review($result['review']),
        ], 201);
    }
}
