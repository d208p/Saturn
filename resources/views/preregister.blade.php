<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-register — Saturn</title>
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
            --red-dim: rgba(248, 113, 113, 0.12);
            --green: #4ade80;
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

        /* Nav */
        nav {
            padding: 24px 0;
        }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .logo span { color: var(--gold); }

        .back-link {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--slate);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }

        .back-link:hover { color: var(--ivory); }

        /* Main */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 40px 0 80px;
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

        .hero {
            position: relative;
            z-index: 1;
            max-width: 620px;
            margin: 0 auto;
            text-align: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--gold-dim);
            border: 1px solid var(--gold-border);
            color: var(--gold);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 100px;
            margin-bottom: 28px;
        }

        .hero-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold);
        }

        h1 {
            font-size: clamp(2.25rem, 5.5vw, 3.5rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .hero-lead {
            font-size: 1.1rem;
            color: var(--slate);
            max-width: 460px;
            margin: 0 auto 40px;
        }

        /* Form */
        .signup-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 36px;
            text-align: left;
        }

        form { display: flex; flex-direction: column; gap: 14px; }

        .field-row {
            display: flex;
            gap: 10px;
        }

        input[type="email"] {
            flex: 1;
            width: 100%;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid var(--card-border);
            background: rgba(0, 0, 0, 0.25);
            color: var(--ivory);
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }

        input[type="email"]::placeholder { color: var(--slate); }

        input[type="email"]:focus {
            border-color: var(--gold-border);
        }

        input[type="email"]:invalid[data-touched="true"] {
            border-color: var(--red);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 26px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            border: none;
            background: var(--gold);
            color: var(--midnight);
            white-space: nowrap;
            transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
        }

        .btn:hover { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(198, 161, 91, 0.3); }
        .btn:disabled { opacity: 0.6; cursor: default; transform: none; box-shadow: none; }

        .fine-print {
            font-size: 0.8rem;
            color: var(--slate);
            margin-top: 2px;
        }

        .form-message {
            font-size: 0.85rem;
            border-radius: 10px;
            padding: 12px 14px;
            display: none;
        }

        .form-message.success {
            display: block;
            background: rgba(74, 222, 128, 0.1);
            border: 1px solid rgba(74, 222, 128, 0.3);
            color: var(--green);
        }

        .form-message.error {
            display: block;
            background: var(--red-dim);
            border: 1px solid rgba(248, 113, 113, 0.3);
            color: var(--red);
        }

        /* State: already signed up */
        .success-state {
            display: none;
            align-items: flex-start;
            gap: 14px;
        }

        .success-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(74, 222, 128, 0.12);
            color: var(--green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .success-state h3 { font-size: 1.05rem; margin-bottom: 4px; }
        .success-state p { color: var(--slate); font-size: 0.9rem; }

        /* Reasons */
        .reasons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 40px;
            text-align: left;
        }

        .reason {
            padding: 18px;
            border-radius: 12px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
        }

        .reason .icon { font-size: 1.1rem; margin-bottom: 8px; }
        .reason h4 { font-size: 0.95rem; margin-bottom: 4px; }
        .reason p { font-size: 0.82rem; color: var(--slate); }

        footer {
            padding: 28px 0;
            border-top: 1px solid var(--card-border);
            text-align: center;
        }

        .disclaimer {
            max-width: 560px;
            margin: 0 auto 8px;
            font-size: 0.72rem;
            color: var(--slate);
            opacity: 0.8;
            line-height: 1.5;
        }

        footer .copyright { font-size: 0.75rem; color: var(--slate); }

        @media (max-width: 640px) {
            .field-row { flex-direction: column; }
            .reasons { grid-template-columns: 1fr; }
            .signup-card { padding: 24px; }
        }
    </style>
</head>
<body>
    <nav>
        <div class="container nav-inner">
            <a href="{{ url('/') }}" class="logo">SA<span>TU</span>RN</a>
            <a href="{{ url('/') }}" class="back-link">&larr; Back to site</a>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="hero">
                <div class="hero-badge">Not yet open to investors</div>
                <h1>Get notified the day Saturn opens.</h1>
                <p class="hero-lead">
                    Leave your email and we'll send you one message when registrations open — nothing before that.
                </p>

                <div class="signup-card">
                    {{-- Server-rendered success message (non-JS fallback) --}}
                    @if(session('success'))
                        <div class="form-message success" style="display:block;">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('preregister.store') }}" method="POST" id="preregister-form">
                        @csrf
                        <div id="form-fields">
                            <div class="field-row">
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    placeholder="you@email.com"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                >
                                <button type="submit" class="btn" id="submit-btn">Notify me</button>
                            </div>

                            @error('email')
                                <div class="form-message error" style="display:block;">{{ $message }}</div>
                            @enderror

                            <p class="fine-print">No spam, no sharing your email. One message, when we launch.</p>
                        </div>
                    </form>

                    {{-- Shown by JS after a successful AJAX submit --}}
                    <div class="success-state" id="success-state">
                        <div class="success-icon">✓</div>
                        <div>
                            <h3>You're on the list.</h3>
                            <p>We'll email you the moment Saturn opens for registrations.</p>
                        </div>
                    </div>
                </div>

                <div class="reasons">
                    <div class="reason">
                        <div class="icon">🥇</div>
                        <h4>Early access</h4>
                        <p>First look at initial assets before they're open to the wider public.</p>
                    </div>
                    <div class="reason">
                        <div class="icon">🛠️</div>
                        <h4>Shape the platform</h4>
                        <p>Early registrants get asked for feedback before things are set in stone.</p>
                    </div>
                    <div class="reason">
                        <div class="icon">📬</div>
                        <h4>One email, that's it</h4>
                        <p>We'll reach out when there's something real to act on — nothing more.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p class="disclaimer">
                Saturn is in pre-launch and not currently open to investors. Registering your interest is not an investment and creates no obligation on either side. Capital will be at risk once investments open.
            </p>
            <div class="copyright">© 2026 Saturn Group</div>
        </div>
    </footer>

    <script>
        // Progressive enhancement: submit via fetch so we can show inline
        // feedback without a full page reload. Falls back to a normal
        // form POST if JS is disabled or the fetch fails unexpectedly.
        const form = document.getElementById('preregister-form');
        const fields = document.getElementById('form-fields');
        const successState = document.getElementById('success-state');
        const emailInput = document.getElementById('email');
        const submitBtn = document.getElementById('submit-btn');

        emailInput.addEventListener('blur', () => emailInput.setAttribute('data-touched', 'true'));

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Remove any previous inline error before retrying
            const existingError = form.querySelector('.form-message.error');
            if (existingError) existingError.remove();

            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending…';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                if (response.ok) {
                    fields.style.display = 'none';
                    successState.style.display = 'flex';
                } else if (response.status === 422) {
                    const data = await response.json();
                    const message = data.errors?.email?.[0] || 'Please enter a valid email address.';
                    const errorEl = document.createElement('div');
                    errorEl.className = 'form-message error';
                    errorEl.style.display = 'block';
                    errorEl.textContent = message;
                    fields.appendChild(errorEl);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Notify me';
                } else {
                    // Unexpected error: let the browser do a normal submit as a fallback
                    form.submit();
                }
            } catch (err) {
                // Network error or JSON endpoint not set up yet: fall back to normal submit
                form.submit();
            }
        });
    </script>
</body>
</html>