<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Verification OTP</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7f6; padding: 30px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0;">
        <div style="background-color: #0f172a; padding: 30px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px;">Flight Booking System</h2>
        </div>
        <div style="padding: 40px 30px; color: #334155; line-height: 1.6;">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 20px; color: #0f172a;">Verify Your Account</h3>
            <p>Thank you for registering. Please use the following One-Time Password (OTP) to verify your account and complete your registration:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <span style="display: inline-block; font-size: 32px; font-weight: 800; color: #06b6d4; background-color: #ecfeff; border: 2px dashed #0891b2; padding: 12px 30px; border-radius: 8px; letter-spacing: 4px;">{{ $otp }}</span>
            </div>
            
            <p style="margin-bottom: 0;">This code is valid for 15 minutes. If you did not request this verification, you can safely ignore this email.</p>
        </div>
        <div style="background-color: #f8fafc; padding: 20px 30px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #f1f5f9;">
            <p style="margin: 0;">&copy; {{ date('Y') }} Flight Booking System Online. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
