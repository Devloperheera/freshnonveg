<?php

namespace App\Services;

use App\Mail\OrderBillMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderBillNotifier
{
    public function sendIfPaid(Order $order): bool
    {
        $order->loadMissing(['items', 'user']);

        if (! in_array($order->status, ['paid', 'confirmed'], true)) {
            return false;
        }

        app(OrderConfirmationSmsService::class)->sendIfPaid($order);
        $order->refresh();

        if ($order->bill_emailed_at) {
            return true;
        }

        $email = trim((string) ($order->user?->email ?? ''));
        if ($email === '') {
            Log::info('Order bill email skipped, user has no email', ['order' => $order->order_number]);

            return false;
        }

        try {
            Mail::to($email)->send(new OrderBillMail($order));
            $order->forceFill(['bill_emailed_at' => now()])->save();

            return true;
        } catch (Throwable $e) {
            Log::error('Order bill email failed', [
                'order' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
