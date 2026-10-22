<?php

namespace App\Http\Controllers\Api\UserDashboard;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\User;
use App\Services\CustomerAccountService;
use App\Support\UserDashboardApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function __construct(private CustomerAccountService $account) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $paginator = $user->complaints()
            ->with('order:id,order_number,total,created_at')
            ->latest('id')
            ->paginate(min(max((int) $request->input('per_page', 10), 1), 50));

        $orderOptions = $user->orders()
            ->latest('id')
            ->limit(30)
            ->get(['id', 'order_number', 'total', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => collect($paginator->items())
                ->map(fn (Complaint $complaint) => UserDashboardApi::complaint($complaint))
                ->values()
                ->all(),
            'order_options' => $orderOptions->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => (float) $order->total,
                'created_at' => $order->created_at?->toIso8601String(),
                'created_at_label' => $order->created_at?->format('d M Y'),
            ])->values()->all(),
            'meta' => UserDashboardApi::meta($paginator),
        ]);
    }

    public function show(Request $request, Complaint $complaint): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless((int) $complaint->user_id === (int) $user->id, 404);

        $complaint->load('order:id,order_number,total,created_at');

        return response()->json([
            'success' => true,
            'data' => UserDashboardApi::complaint($complaint),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'order_id' => ['nullable', 'integer', Rule::exists('orders', 'id')->where('user_id', $user->id)],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $complaint = $this->account->storeComplaint($user, $data);
        $complaint->load('order:id,order_number,total,created_at');

        return response()->json([
            'success' => true,
            'message' => 'Complaint register ho gayi. Team jald review karegi — sahi paaya gaya to wallet me amount credit hoga.',
            'data' => UserDashboardApi::complaint($complaint),
        ], 201);
    }
}
