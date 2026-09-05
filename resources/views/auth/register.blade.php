<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Saturn</title>
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

        header { padding: 24px 0; }

        .header-inner {
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

        .logo-img { max-width: 40px; }

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
            max-width: 440px;
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
            background: var(--red-dim);
            border: 1px solid rgba(248, 113, 113, 0.3);
            color: var(--red);
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        .alert ul { list-style: none; }
        .alert li { padding: 2px 0; }
        .alert li::before { content: '⚠ '; }

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
            font-size: 0.78rem;
            color: var(--slate);
            margin-top: 6px;
        }

        .field-hint.field-hint-error { color: var(--red); }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
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

        .terms-note {
            font-size: 0.78rem;
            color: var(--slate);
        }

        .terms-note a {
            color: var(--gold);
        }

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
            .field-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-inner">
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('images/v5yuA.png') }}" alt="Saturn Logo" class="logo-img">
                <img src="{{ asset('images/text.svg') }}" alt="" style="max-width: 150px;">
            </a>
            <a href="/" class="back-link">&larr; Back to site</a>
        </div>
    </header>

    <main>
        <div class="auth-card">
            <h1>Create an account</h1>
            <p class="subhead">Set up your Saturn account to get started.</p>

            {{-- Display validation errors --}}
            @if ($errors->any())
                <div class="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/register" method="POST" id="register-form">
                @csrf

                <div class="field">
                    <label for="name">Full name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="{{ $errors->has('name') ? 'field-error' : '' }}"
                        value="{{ old('name') }}"
                        placeholder="Jane Doe"
                        required
                        autofocus
                        autocomplete="name"
                    >
                </div>

                <div class="field">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="{{ $errors->has('email') ? 'field-error' : '' }}"
                        value="{{ old('email') }}"
                        placeholder="you@email.com"
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="{{ $errors->has('password') ? 'field-error' : '' }}"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                        >
                        <p class="field-hint">At least 8 characters</p>
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                        >
                    </div>
                </div>

                <button type="submit" class="btn-primary" id="register-btn">
                    <span class="spinner" id="register-spinner"></span>
                    <span id="register-btn-text">Create account</span>
                </button>

                <p class="terms-note">
                    By creating an account you agree to Saturn's <a href="/terms">Terms</a> and <a href="/privacy">Privacy Policy</a>.
                </p>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="{{ route('login') }}">Log in here</a>
            </div>
        </div>
    </main>

    <footer class="page-footer">
        <p class="disclaimer">© 2026 Saturn Group · Creating an account does not make you an investor. Investing happens separately, once available.</p>
    </footer>

    <script>
        // Cosmetic loading state only — validation and account creation
        // happen server-side. On failure Laravel returns back with
        // $errors, which the alert block above already renders (with
        // old('name') / old('email') preserved). On success your
        // RegisteredUserController decides where to redirect
        // (commonly /dashboard after logging the user in).
        const form = document.getElementById('register-form');
        const btn = document.getElementById('register-btn');
        const spinner = document.getElementById('register-spinner');
        const btnText = document.getElementById('register-btn-text');

        form.addEventListener('submit', () => {
            btn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.textContent = 'Creating account…';
        });
    </script>
</body>
</html>