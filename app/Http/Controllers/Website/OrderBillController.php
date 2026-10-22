<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderBillNotifier;
use Illuminate\View\View;

class OrderBillController extends Controller
{
    public function show(Order $order, OrderBillNotifier $bills): View
    {
        if ((int) $order->user_id !== (int) auth()->id()) {
            abort(403);
        }

        if (! in_array($order->status, ['paid', 'confirmed', 'cancelled'], true)) {
            abort(404);
        }

        $order->load(['items', 'user']);
        if ($order->isPaid()) {
            $bills->sendIfPaid($order);
            $order->refresh();
        }

        return view('Website.order-bill', compact('order'));
    }
}
