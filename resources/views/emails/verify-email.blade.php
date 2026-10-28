<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background-color: #0d1117; color: #e6edf3; padding: 40px; }
        .card { background: #161b22; border: 1px solid #30363d; border-radius: 8px; padding: 32px; max-width: 500px; margin: 0 auto; }
        .btn { display: inline-block; background: #C6A15B; color: #0B1220; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Verify Your Email Address</h2>
        <p>Hello {{ $userName }}, please click the button below to verify your email address for Saturn.</p>
        <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
        <p style="color: #8b949e; font-size: 13px;">If you did not create an account, no further action is required.</p>
    </div>
</body>
</html>