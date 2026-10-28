<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0d1117; color: #e6edf3; margin: 0; padding: 40px; }
        .card { background: #161b22; border: 1px solid #30363d; border-radius: 8px; padding: 32px; max-width: 500px; margin: 0 auto; }
        .title { font-size: 20px; font-weight: 700; color: #f0f6fc; margin-bottom: 16px; }
        .muted { color: #8b949e; font-size: 14px; line-height: 1.5; }
        .btn { display: inline-block; background: #C6A15B; color: #0B1220; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; margin: 20px 0; }
        .footer { margin-top: 24px; font-size: 12px; color: #484f58; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="title">Reset Your Saturn Password</div>
        <p class="muted">Hello {{ $userName ?? 'User' }},</p>
        <p class="muted">We received a request to reset the password for your Saturn account. Click the button below to set a new password:</p>
        
        <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
        
        <p class="muted">This password reset link will expire in 60 minutes. If you did not request a password reset, no further action is required.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} Saturn Platform. All rights reserved.
        </div>
    </div>
</body>
</html>