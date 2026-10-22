@php $user = $txn->user; @endphp
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Wallet credit</title></head>
<body style="margin:0;padding:24px 12px;background:#f4f6f3;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
<tr><td align="center">
<table role="presentation" width="520" cellpadding="0" cellspacing="0" style="max-width:520px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
    <tr><td style="height:5px;background:linear-gradient(90deg,#D32F2F,#F57C00,#2BAE49);background-color:#2BAE49;"></td></tr>
    <tr>
        <td style="padding:22px 24px;text-align:center;">
            <img src="{{ asset('website_assets/image/logo.png') }}" alt="FreshNonVeg" height="40" style="height:40px;">
            <p style="margin:16px 0 6px;font-size:14px;">Namaste {{ $user?->name ?? 'Customer' }},</p>
            <p style="margin:0 0 14px;font-size:14px;color:#374151;">Aapke FreshNonVeg wallet me amount add kiya gaya hai.</p>
            <div style="background:linear-gradient(135deg,#D32F2F,#F57C00,#2BAE49);background-color:#2BAE49;color:#fff;border-radius:12px;padding:18px;">
                <div style="font-size:30px;font-weight:bold;">+₹{{ number_format((float) $txn->amount, 2) }}</div>
                <div style="font-size:13px;opacity:.95;margin-top:4px;">Naya balance: ₹{{ number_format((float) $txn->balance_after, 2) }}</div>
            </div>
            @if(filled($note))
                <p style="margin:14px 0 0;font-size:13px;color:#374151;white-space:pre-line;">{{ $note }}</p>
            @endif
            <p style="margin:18px 0 0;">
                <a href="{{ url('/dashboard/wallet') }}" style="display:inline-block;background:#2BAE49;color:#fff;text-decoration:none;font-weight:bold;padding:10px 20px;border-radius:8px;font-size:14px;">Wallet dekhein</a>
            </p>
            <p style="margin:12px 0 0;font-size:12px;color:#6b7280;">Checkout par "Use wallet balance" chun kar is amount ko use karein.</p>
        </td>
    </tr>
    <tr><td style="padding:0 24px 22px;font-size:12px;color:#6b7280;text-align:center;">— FreshNonVeg Team</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
