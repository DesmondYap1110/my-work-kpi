<!doctype html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; background: #f4f6f9; padding: 24px;">
    <div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 24px;">
        <h2 style="margin-top: 0;">Welcome to {{ config('app.name') }}</h2>
        <p>Hi {{ $staff->staff_name }},</p>
        <p>An account has been created for you on {{ config('app.name') }}. Click the button below to set your password and get started.</p>
        <p style="text-align: center; margin: 32px 0;">
            <a href="{{ $setPasswordUrl }}" style="background: #0d6efd; color: #fff; padding: 12px 24px; border-radius: 4px; text-decoration: none;">Set Your Password</a>
        </p>
        <p>Your login email is: <strong>{{ $staff->email }}</strong></p>
        <p style="color: #6c757d; font-size: 13px;">If you did not expect this email, you can safely ignore it.</p>
    </div>
</body>
</html>
