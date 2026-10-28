<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email — Saturn</title>
    <link rel="icon" type="image/png" href="{{ asset('images/tab.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --midnight: #0B1220; --ivory: #F5F2EA; --gold: #C6A15B; --slate: #667085; --card-bg: rgba(255, 255, 255, 0.03); --card-border: rgba(255, 255, 255, 0.06); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Manrope', system-ui, sans-serif; background: var(--midnight); color: var(--ivory); min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 24px; }
        .auth-card { width: 100%; max-width: 420px; background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 40px; text-align: center; }
        h1 { font-size: 1.5rem; margin-bottom: 12px; }
        p { color: var(--slate); font-size: 0.95rem; margin-bottom: 24px; line-height: 1.5; }
        .btn-primary { width: 100%; padding: 13px; border-radius: 10px; font-weight: 600; border: none; background: var(--gold); color: var(--midnight); cursor: pointer; }
        .alert { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.3); color: #4ade80; border-radius: 10px; padding: 12px; font-size: 0.85rem; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h1>Verify your email</h1>
        <p>Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?</p>

        @if (session('status') == 'verification-link-sent')
            <div class="alert">A new verification link has been sent to your email address.</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary">Resend Verification Email</button>
        </form>
    </div>
</body>
</html>