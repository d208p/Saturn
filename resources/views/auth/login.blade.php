<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in — Saturn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --midnight: #0B1220;
            --ivory: #F5F2EA;
            --gold: #C6A15B;
            --slate: #667085;
            --gold-dim: rgba(198, 161, 91, 0.15);
            --gold-border: rgba(198, 161, 91, 0.35);
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.06);
            --red: #f87171;
            --red-dim: rgba(248, 113, 113, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Manrope', system-ui, sans-serif;
            background: var(--midnight);
            color: var(--ivory);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a { color: inherit; text-decoration: none; }

        .container {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 24px;
        }

        nav { padding: 24px 0; }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo span { color: var(--gold); }

        .back-link {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--slate);
            transition: color 0.2s;
        }

        .back-link:hover { color: var(--ivory); }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 32px 24px 80px;
        }

        main::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 60%;
            height: 80%;
            background: radial-gradient(ellipse, rgba(198, 161, 91, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 40px;
        }

        .auth-card h1 {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .auth-card .subhead {
            color: var(--slate);
            font-size: 0.95rem;
            margin-bottom: 28px;
        }

        .alert {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            background: var(--red-dim);
            border: 1px solid rgba(248, 113, 113, 0.3);
            color: var(--red);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        form { display: flex; flex-direction: column; gap: 18px; }

        .field label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--ivory);
        }

        .field input {
            width: 100%;
            padding: 13px 14px;
            border-radius: 10px;
            border: 1px solid var(--card-border);
            background: rgba(0, 0, 0, 0.25);
            color: var(--ivory);
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .field input::placeholder { color: var(--slate); }
        .field input:focus { border-color: var(--gold-border); }
        .field input.field-error { border-color: var(--red); }

        .field-hint {
            font-size: 0.8rem;
            color: var(--red);
            margin-top: 6px;
        }

        .password-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .password-row label { margin-bottom: 0; }

        .forgot-link {
            font-size: 0.82rem;
            color: var(--gold);
            font-weight: 500;
        }

        .forgot-link:hover { text-decoration: underline; }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--slate);
        }

        .remember-row input {
            width: 16px;
            height: 16px;
            accent-color: var(--gold);
        }

        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 24px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            border: none;
            background: var(--gold);
            color: var(--midnight);
            transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
        }

        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(198, 161, 91, 0.3); }
        .btn-primary:disabled { opacity: 0.65; cursor: default; transform: none; box-shadow: none; }

        .spinner {
            width: 15px;
            height: 15px;
            border: 2px solid rgba(11, 18, 32, 0.3);
            border-top-color: var(--midnight);
            border-radius: 50%;
            display: none;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .auth-footer {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--card-border);
            text-align: center;
            font-size: 0.88rem;
            color: var(--slate);
        }

        .auth-footer a {
            color: var(--gold);
            font-weight: 600;
        }

        .auth-footer a:hover { text-decoration: underline; }

        footer.page-footer {
            padding: 24px 0;
            text-align: center;
        }

        .disclaimer {
            max-width: 480px;
            margin: 0 auto;
            font-size: 0.72rem;
            color: var(--slate);
            opacity: 0.75;
        }

        .logo-img {
            max-width: 60px;
        }

        @media (max-width: 480px) {
            .auth-card { padding: 28px 24px; }
        }
    </style>
</head>
<body>
    <nav>
        <div class="container nav-inner">
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('images/v5yuA.png') }}" alt="Saturn Logo" class="logo-img">
                <img src="{{ asset('images/text.svg') }}" alt="" style="max-width: 150px;">
            </a>
            <a href="{{ url('/') }}" class="back-link">&larr; Back to site</a>
        </div>
    </nav>

    <main>
        <div class="auth-card">
            <h1>Welcome back</h1>
            <p class="subhead">Log in to your Saturn account.</p>

            {{-- Shown when the credentials don't match, e.g. from the
                 AuthController: back()->withErrors(['email' => '...']) --}}
            @if ($errors->has('email') && $errors->first('email') !== old('email'))
                <div class="alert">⚠ {{ $errors->first('email') }}</div>
            @endif
            @if (session('status'))
                <div class="alert" style="background: rgba(74,222,128,0.1); border-color: rgba(74,222,128,0.3); color: #4ade80;">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" id="login-form">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="{{ $errors->has('email') ? 'field-error' : '' }}"
                        value="{{ old('email') }}"
                        placeholder="you@email.com"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="field">
                    <div class="password-row">
                        <label for="password">Password</label>
                        {{-- <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a> --}}
                    </div>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="{{ $errors->has('password') ? 'field-error' : '' }}"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <div class="field-hint">{{ $message }}</div>
                    @enderror
                </div>

                <label class="remember-row">
                    <input type="checkbox" name="remember" id="remember">
                    Keep me logged in
                </label>

                <button type="submit" class="btn-primary" id="login-btn">
                    <span class="spinner" id="login-spinner"></span>
                    <span id="login-btn-text">Log in</span>
                </button>
            </form>

            <div class="auth-footer">
                Not registered yet? <a href="{{ route('register') }}">Join the waitlist</a>
            </div>
        </div>
    </main>

    <footer class="page-footer">
        <p class="disclaimer">© 2026 Saturn Group · Your session is protected and never shared with third parties.</p>
    </footer>

    <script>
        // Cosmetic only: the actual success/failure branching happens on
        // the server. On success, Laravel's AuthController redirects with
        // `return redirect()->intended('/dashboard');`. On failure it
        // returns back with `withErrors(['email' => '...'])`, which the
        // Blade block above already renders. This script just shows a
        // loading state while the real page navigation happens.
        const form = document.getElementById('login-form');
        const btn = document.getElementById('login-btn');
        const spinner = document.getElementById('login-spinner');
        const btnText = document.getElementById('login-btn-text');

        form.addEventListener('submit', () => {
            btn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.textContent = 'Logging in…';
        });
    </script>
</body>
</html>