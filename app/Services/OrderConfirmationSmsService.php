<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderConfirmationSmsService
{
    public function __construct(private Fast2SmsService $fast2sms) {}

    public function sendIfPaid(Order $order): bool
    {
        $order->loadMissing('user');

        if (! in_array($order->status, Order::PAID_STATUSES, true)) {
            return false;
        }

        if ($order->confirmation_sms_sent_at) {
            return true;
        }

        $mobile = $this->normalizeMobile((string) ($order->phone ?: $order->user?->phone));
        if ($mobile === null) {
            return false;
        }

        if (! $this->fast2sms->isConfigured()) {
            return false;
        }

        $messageId = (string) config('services.fast2sms.order_message_id');
        if ($messageId === '') {
            return false;
        }

        $variables = implode('|', [
            $order->order_number,
            $this->paymentLabel($order),
            Str::limit(trim((string) ($order->user?->name ?: 'Customer')), 40, ''),
            number_format((float) $order->total, 2, '.', ''),
            $order->created_at?->timezone(config('app.timezone'))->format('d M Y') ?? now()->format('d M Y'),
            $mobile,
        ]);

        $result = $this->fast2sms->sendDlt(
            $mobile,
            $messageId,
            $variables,
            config('services.fast2sms.order_dlt_template_id') ? (string) config('services.fast2sms.order_dlt_template_id') : null,
        );

        if ($result['ok']) {
            $order->forceFill(['confirmation_sms_sent_at' => now()])->save();

            return true;
        }

        return false;
    }

    private function paymentLabel(Order $order): string
    {
        return match ($order->payment_method) {
            'cashfree' => 'Online Paid',
            default => 'Paid',
        };
    }

    private function normalizeMobile(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        return strlen($digits) === 10 ? $digits : null;
    }
}
