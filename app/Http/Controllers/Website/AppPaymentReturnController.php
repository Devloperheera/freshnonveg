<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AppPaymentReturnController extends Controller
{
    public function show(string $order_number, string $status = 'pending'): View
    {
        $deepLinkBase = rtrim((string) config('services.mobile_app.payment_deep_link', 'freshnonveg://payment/return'), '/');
        $deepLink = $deepLinkBase.'?'.http_build_query([
            'order_number' => $order_number,
            'status' => $status,
        ]);

        return view('Website.app-payment-return', [
            'orderNumber' => $order_number,
            'status' => $status,
            'deepLink' => $deepLink,
            'isPaid' => $status === 'paid',
        ]);
    }
}