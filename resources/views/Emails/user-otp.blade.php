<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FreshNonVeg OTP</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #111827;">
    <p>Hello {{ $name }},</p>
    <p>Your OTP is:</p>
    <p style="font-size: 28px; font-weight: bold; letter-spacing: 4px;">{{ $otp }}</p>
    <p>This OTP is valid for 10 minutes. Agar aapne request nahi ki, is email ko ignore karein.</p>
    <p>— FreshNonVeg Team</p>
</body>
</html>
