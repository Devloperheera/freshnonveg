<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderNotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::query()
            ->with('user:id,name,phone')
            ->whereNull('admin_seen_at')
            ->whereIn('status', Order::PAID_STATUSES)
            ->latest('id')
            ->limit(40)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => (float) $order->total,
                'status' => $order->status,
                'customer' => $order->user?->name ?: 'Guest',
                'phone' => $order->user?->phone ?: $order->phone,
                'created_at' => $order->created_at?->timezone(config('app.timezone'))->format('d M, h:i A'),
                'url' => route('admin.orders.show', $order, false),
            ]);

        return response()->json([
            'count' => $orders->count(),
            'orders' => $orders->values(),
        ]);
    }

    public function markSeen(Request $request): JsonResponse
    {
        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $query = Order::query()->whereNull('admin_seen_at');

        if ($request->boolean('all')) {
            $query->whereIn('status', Order::PAID_STATUSES)->update(['admin_seen_at' => now()]);
        } elseif ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids)->update(['admin_seen_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }
}
