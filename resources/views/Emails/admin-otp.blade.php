<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #172019;">
    <p>Hello {{ $name }},</p>
    <p>Your password reset OTP is:</p>
    <p style="font-size: 28px; font-weight: bold; letter-spacing: 4px;">{{ $otp }}</p>
    <p>This OTP is valid for a short time. If you did not request a reset, you can ignore this email.</p>
</body>
</html>
