<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication — Saturn</title>
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
            font-size: 1.1rem;
            text-align: center;
            letter-spacing: 3px;
            outline: none;
            transition: border-color 0.2s;
        }

        .field input::placeholder { color: var(--slate); letter-spacing: normal; }
        .field input:focus { border-color: var(--gold-border); }

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
            width: 100%;
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

        .toggle-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--gold);
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: color 0.2s;
        }

        .toggle-link:hover { text-decoration: underline; }

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
            <a href="{{ route('login') }}" class="back-link">&larr; Back to log in</a>
        </div>
    </nav>

    <main>
        <div class="auth-card">
            <h1 id="form-title">Two-Factor Challenge</h1>
            <p class="subhead" id="form-subhead">Please enter the 6-digit code from your authenticator app.</p>

            @if ($errors->any())
                <div class="alert">
                    ⚠ {{ $errors->first('code') ?: $errors->first('recovery_code') ?: 'Invalid code provided. Please try again.' }}
                </div>
            @endif

            {{-- Standard TOTP Form --}}
            <form action="{{ route('two-factor.login') }}" method="POST" id="totp-form">
                @csrf
                <div class="field">
                    <label for="code">Authenticator code</label>
                    <input
                        type="text"
                        name="code"
                        id="code"
                        placeholder="123456"
                        autofocus
                        autocomplete="one-time-code"
                        maxlength="6"
                    >
                </div>

                <button type="submit" class="btn-primary" id="totp-btn">
                    <span class="spinner" id="totp-spinner"></span>
                    <span id="totp-btn-text">Verify Code</span>
                </button>
            </form>

            {{-- Recovery Code Form (Hidden by default) --}}
            <form action="{{ route('two-factor.login') }}" method="POST" id="recovery-form" style="display: none;">
                @csrf
                <div class="field">
                    <label for="recovery_code">Recovery code</label>
                    <input
                        type="text"
                        name="recovery_code"
                        id="recovery_code"
                        placeholder="xxxx-xxxxxx"
                        style="letter-spacing: 1px;"
                    >
                </div>

                <button type="submit" class="btn-primary" id="recovery-btn">
                    <span class="spinner" id="recovery-spinner"></span>
                    <span id="recovery-btn-text">Verify Recovery Code</span>
                </button>
            </form>

            {{-- Toggle Button --}}
            <a class="toggle-link" id="toggle-btn">Lost access? Use a recovery code</a>
        </div>
    </main>

    <footer class="page-footer">
        <p class="disclaimer">© 2026 Saturn Group · Your session is protected and never shared with third parties.</p>
    </footer>

    <script>
        const toggleBtn = document.getElementById('toggle-btn');
        const totpForm = document.getElementById('totp-form');
        const recoveryForm = document.getElementById('recovery-form');
        const formTitle = document.getElementById('form-title');
        const formSubhead = document.getElementById('form-subhead');

        // Form switching logic
        toggleBtn.addEventListener('click', () => {
            if (totpForm.style.display !== 'none') {
                totpForm.style.display = 'none';
                recoveryForm.style.display = 'flex';
                formTitle.textContent = 'Recovery Code Login';
                formSubhead.textContent = 'Enter one of your emergency backup recovery codes.';
                toggleBtn.textContent = 'Use authenticator code instead';
                document.getElementById('recovery_code').focus();
            } else {
                totpForm.style.display = 'flex';
                recoveryForm.style.display = 'none';
                formTitle.textContent = 'Two-Factor Challenge';
                formSubhead.textContent = 'Please enter the 6-digit code from your authenticator app.';
                toggleBtn.textContent = 'Lost access? Use a recovery code';
                document.getElementById('code').focus();
            }
        });

        // Loading states on form submit
        totpForm.addEventListener('submit', () => {
            const btn = document.getElementById('totp-btn');
            const spinner = document.getElementById('totp-spinner');
            const btnText = document.getElementById('totp-btn-text');
            btn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.textContent = 'Verifying…';
        });

        recoveryForm.addEventListener('submit', () => {
            const btn = document.getElementById('recovery-btn');
            const spinner = document.getElementById('recovery-spinner');
            const btnText = document.getElementById('recovery-btn-text');
            btn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.textContent = 'Verifying…';
        });
    </script>
</body>
</html>