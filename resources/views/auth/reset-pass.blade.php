<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password — Saturn</title>
    <link rel="icon" type="image/png" href="{{ asset('images/tab.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --midnight: #0B1220;
            --ivory: #F5F2EA;
            --gold: #C6A15B;
            --slate: #667085;
            --gold-border: rgba(198, 161, 91, 0.35);
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.06);
            --red: #f87171;
            --red-dim: rgba(248, 113, 113, 0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Manrope', system-ui, sans-serif; background: var(--midnight); color: var(--ivory); line-height: 1.6; min-height: 100vh; display: flex; flex-direction: column; }
        a { color: inherit; text-decoration: none; }
        .container { width: 100%; max-width: 1120px; margin: 0 auto; padding: 0 24px; }
        nav { padding: 24px 0; }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; }
        .logo { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.03em; display: flex; align-items: center; gap: 10px; }
        main { flex: 1; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; padding: 32px 24px 80px; }
        .auth-card { position: relative; z-index: 1; width: 100%; max-width: 400px; background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 40px; }
        .auth-card h1 { font-size: 1.6rem; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 6px; }
        .auth-card .subhead { color: var(--slate); font-size: 0.95rem; margin-bottom: 28px; }
        .alert { display: flex; gap: 10px; align-items: flex-start; background: var(--red-dim); border: 1px solid rgba(248, 113, 113, 0.3); color: var(--red); border-radius: 10px; padding: 12px 14px; font-size: 0.85rem; margin-bottom: 20px; }
        form { display: flex; flex-direction: column; gap: 18px; }
        .field label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: var(--ivory); }
        .field input { width: 100%; padding: 13px 14px; border-radius: 10px; border: 1px solid var(--card-border); background: rgba(0, 0, 0, 0.25); color: var(--ivory); font-family: inherit; font-size: 0.95rem; outline: none; transition: border-color 0.2s; }
        .field input:focus { border-color: var(--gold-border); }
        .btn-primary { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 13px 24px; border-radius: 10px; font-size: 0.95rem; font-weight: 600; font-family: inherit; cursor: pointer; border: none; background: var(--gold); color: var(--midnight); transition: transform 0.2s, box-shadow 0.2s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(198, 161, 91, 0.3); }
        .logo-img { max-width: 60px; }
    </style>
</head>
<body>
    <nav>
        <div class="container nav-inner">
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('images/v5yuA.png') }}" alt="Saturn Logo" class="logo-img">
                <img src="{{ asset('images/text.svg') }}" alt="" style="max-width: 150px;">
            </a>
        </div>
    </nav>

    <main>
        <div class="auth-card">
            <h1>Set new password</h1>
            <p class="subhead">Enter your new password below.</p>

            @if ($errors->any())
                <div class="alert">⚠ {{ $errors->first() }}</div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="field">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required placeholder="••••••••">
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn-primary">Reset Password</button>
            </form>
        </div>
    </main>
</body>
</html>