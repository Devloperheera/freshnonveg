<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:#fff8f3;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fff8f3;padding:24px 12px;">
        <tr>
            <td align="center">
                <div style="max-width:420px;margin:0 auto 16px;font-family:Arial,Helvetica,sans-serif;color:#111827;text-align:left;">
                    <p style="margin:0 0 8px;font-size:16px;">Namaste {{ $order->user?->name ?? 'Customer' }},</p>
                    <p style="margin:0 0 8px;font-size:15px;line-height:1.5;">
                        Aapka order confirm ho gaya hai. <strong>Order number: {{ $order->order_number }}</strong>
                    </p>
                    <p style="margin:0 0 16px;font-size:14px;line-height:1.5;color:#374151;">
                        Aapne ye order kiya hai. Neeche aapka food bill hai — isi bill ki copy aapke dashboard mein bhi hai.
                    </p>
                </div>
                @include('Website.partials.food-bill', ['order' => $order])
                <p style="max-width:420px;margin:16px auto 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6b7280;text-align:center;">
                    Bill dashboard: {{ route('orders.bill', $order) }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
