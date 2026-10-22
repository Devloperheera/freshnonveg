@php
    $c = $complaint;
    $user = $c->user;
@endphp
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Complaint #{{ $c->id }}</title></head>
<body style="margin:0;padding:24px 12px;background:#f4f6f3;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
<tr><td align="center">
<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
    <tr><td style="height:5px;background:linear-gradient(90deg,#D32F2F,#F57C00,#2BAE49);background-color:#2BAE49;"></td></tr>
    <tr>
        <td style="padding:22px 24px 8px;">
            <img src="{{ asset('website_assets/image/logo.png') }}" alt="FreshNonVeg" height="40" style="height:40px;">
            <h2 style="margin:14px 0 4px;font-size:20px;color:#1E8E3E;">Aapki complaint ka jawab</h2>
            <p style="margin:0;font-size:13px;color:#6b7280;">Complaint #{{ $c->id }} · {{ $c->statusLabel() }}</p>
        </td>
    </tr>
    <tr>
        <td style="padding:12px 24px;">
            <p style="margin:0 0 12px;font-size:14px;">Namaste {{ $user?->name ?? 'Customer' }},</p>
            <div style="background:#f9fafb;border-left:4px solid #9ca3af;border-radius:6px;padding:10px 12px;margin-bottom:14px;">
                <div style="font-size:11px;font-weight:bold;color:#6b7280;text-transform:uppercase;">Aapki complaint</div>
                <div style="font-size:14px;font-weight:bold;margin-top:3px;">{{ $c->subject }}</div>
                @if($c->order)<div style="font-size:12px;color:#6b7280;">Order {{ $c->order->order_number }}</div>@endif
                <div style="font-size:13px;margin-top:6px;white-space:pre-line;">{{ \Illuminate\Support\Str::limit($c->message, 500) }}</div>
            </div>
            @if(filled($c->admin_reply))
                <div style="background:#f0fdf4;border-left:4px solid #2BAE49;border-radius:6px;padding:10px 12px;margin-bottom:14px;">
                    <div style="font-size:11px;font-weight:bold;color:#1E8E3E;text-transform:uppercase;">FreshNonVeg team ka jawab</div>
                    <div style="font-size:14px;margin-top:6px;white-space:pre-line;">{{ $c->admin_reply }}</div>
                </div>
            @endif
            @if($creditedNow > 0)
                <div style="background:linear-gradient(135deg,#D32F2F,#F57C00);background-color:#F57C00;color:#fff;border-radius:10px;padding:14px 16px;margin-bottom:14px;">
                    <div style="font-size:12px;opacity:.9;">Compensation wallet me add hua</div>
                    <div style="font-size:26px;font-weight:bold;">+₹{{ number_format($creditedNow, 2) }}</div>
                    <div style="font-size:12px;opacity:.9;">Naya wallet balance: ₹{{ number_format((float) $user?->wallet_balance, 2) }} — agle order par checkout me use karein.</div>
                </div>
            @endif
            <p style="margin:14px 0 0;text-align:center;">
                <a href="{{ url('/dashboard/complaints') }}" style="display:inline-block;background:#2BAE49;color:#fff;text-decoration:none;font-weight:bold;padding:10px 20px;border-radius:8px;font-size:14px;">Meri complaints dekhein</a>
            </p>
        </td>
    </tr>
    <tr><td style="padding:16px 24px 22px;font-size:12px;color:#6b7280;text-align:center;">Dhanyavaad! — FreshNonVeg Team</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
