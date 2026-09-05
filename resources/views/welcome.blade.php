<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Saturn</title>
    <link rel="icon" type="image/png" href="{{ asset('images/v5yuA.png') }}">
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Manrope', system-ui, sans-serif;
            background: var(--midnight);
            color: var(--ivory);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Typography */
        h1, h2, h3, h4 {
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Layout helpers */
        .container {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .section {
            padding: 100px 0;
        }

        .section-label {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 16px;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 2.75rem);
            margin-bottom: 20px;
            max-width: 640px;
        }

        .section-lead {
            font-size: 1.125rem;
            color: var(--slate);
            max-width: 560px;
            margin-bottom: 48px;
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 20px 0;
            background: rgba(11, 18, 32, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid transparent;
            transition: border-color 0.3s, padding 0.3s;
        }

        nav.scrolled {
            border-bottom-color: var(--card-border);
            padding: 14px 0;
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
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-mark {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold), #a8843f);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--midnight);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .nav-links a {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--slate);
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: var(--ivory);
        }

        .nav-cta {
            background: var(--gold);
            color: var(--midnight) !important;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600 !important;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .nav-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(198, 161, 91, 0.3);
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--ivory);
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Hero */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding-top: 80px;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 60%;
            height: 80%;
            background: radial-gradient(ellipse, rgba(198, 161, 91, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 720px;
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

        .hero h1 {
            font-size: clamp(3rem, 8vw, 5.5rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.05;
            margin-bottom: 24px;
        }

        .hero h1 span {
            color: var(--gold);
        }

        .hero-tagline {
            font-size: clamp(1.25rem, 2.5vw, 1.6rem);
            font-weight: 400;
            color: var(--slate);
            margin-bottom: 40px;
            font-style: italic;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: var(--gold);
            color: var(--midnight);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(198, 161, 91, 0.35);
        }

        .btn-secondary {
            background: transparent;
            color: var(--ivory);
            border: 1px solid var(--card-border);
        }

        .btn-secondary:hover {
            border-color: var(--slate);
            background: var(--card-bg);
        }

        /* Concept */
        .concept-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: start;
        }

        .concept-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 32px;
        }

        .concept-card h3 {
            font-size: 1.15rem;
            margin-bottom: 12px;
            color: var(--gold);
        }

        .concept-card p {
            color: var(--slate);
            font-size: 0.95rem;
        }

        .concept-list {
            list-style: none;
            margin-top: 24px;
        }

        .concept-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--card-border);
            font-size: 0.95rem;
        }

        .concept-list li:last-child {
            border-bottom: none;
        }

        .check {
            color: var(--gold);
            font-weight: 700;
            flex-shrink: 0;
        }

        /* Structure diagram */
        .structure {
            background: linear-gradient(180deg, transparent, rgba(198, 161, 91, 0.03), transparent);
        }

        .org-chart {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            margin-top: 48px;
        }

        .org-node {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 16px 28px;
            text-align: center;
            min-width: 200px;
            position: relative;
        }

        .org-node.primary {
            border-color: var(--gold-border);
            background: var(--gold-dim);
        }

        .org-node .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--slate);
            margin-bottom: 4px;
        }

        .org-node .name {
            font-weight: 700;
            font-size: 1.05rem;
        }

        .org-connector {
            width: 2px;
            height: 28px;
            background: var(--gold-border);
        }

        .org-row {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .org-branch {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Categories */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: 48px;
        }

        .cat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 32px 28px;
            transition: border-color 0.25s, transform 0.25s;
        }

        .cat-card:hover {
            border-color: var(--gold-border);
            transform: translateY(-4px);
        }

        .cat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--gold-dim);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 20px;
        }

        .cat-card h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
        }

        .cat-card p {
            color: var(--slate);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .cat-examples {
            list-style: none;
            font-size: 0.85rem;
            color: var(--slate);
        }

        .cat-examples li {
            padding: 4px 0;
            padding-left: 14px;
            position: relative;
        }

        .cat-examples li::before {
            content: '·';
            position: absolute;
            left: 0;
            color: var(--gold);
            font-weight: 700;
        }

        /* Process steps */
        .process-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 48px;
            counter-reset: step;
        }

        .step {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 28px 22px;
            position: relative;
            counter-increment: step;
        }

        .step::before {
            content: counter(step, decimal-leading-zero);
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--gold);
            letter-spacing: 0.05em;
            display: block;
            margin-bottom: 12px;
        }

        .step h4 {
            font-size: 1.05rem;
            margin-bottom: 8px;
        }

        .step p {
            font-size: 0.875rem;
            color: var(--slate);
        }

        /* Portfolio mock */
        .portfolio-mock {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 40px;
            margin-top: 48px;
            max-width: 480px;
        }

        .portfolio-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
        }

        .portfolio-total {
            font-size: 2rem;
            font-weight: 700;
        }

        .portfolio-change {
            color: #4ade80;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .portfolio-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat {
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
            padding: 14px 16px;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--slate);
            margin-bottom: 4px;
        }

        .stat-value {
            font-weight: 600;
            font-size: 1.05rem;
        }

        .asset-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 0;
            border-top: 1px solid var(--card-border);
        }

        .asset-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--gold-dim);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .asset-info {
            flex: 1;
        }

        .asset-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .asset-meta {
            font-size: 0.8rem;
            color: var(--slate);
        }

        .asset-amount {
            font-weight: 600;
            text-align: right;
        }

        .asset-yield {
            font-size: 0.8rem;
            color: #4ade80;
        }

        /* Two column feature */
        .feature-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 64px;
            align-items: center;
            margin-top: 64px;
        }

        .feature-row.reverse {
            direction: rtl;
        }

        .feature-row.reverse > * {
            direction: ltr;
        }

        .feature-visual {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 36px;
            min-height: 280px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .risk-bar {
            display: flex;
            gap: 6px;
            margin: 12px 0 24px;
        }

        .risk-segment {
            flex: 1;
            height: 6px;
            border-radius: 3px;
            background: var(--card-border);
        }

        .risk-segment.active {
            background: var(--gold);
        }

        .scenario-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 16px;
        }

        .scenario {
            text-align: center;
            padding: 14px 8px;
            background: rgba(0,0,0,0.25);
            border-radius: 10px;
        }

        .scenario-label {
            font-size: 0.7rem;
            color: var(--slate);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .scenario-value {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .scenario.down .scenario-value { color: #f87171; }
        .scenario.base .scenario-value { color: var(--gold); }
        .scenario.up .scenario-value { color: #4ade80; }

        /* Principles */
        .principles-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-top: 48px;
        }

        .principle {
            display: flex;
            gap: 18px;
            padding: 24px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
        }

        .principle-num {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gold);
            opacity: 0.6;
            line-height: 1;
        }

        .principle h4 {
            font-size: 1.05rem;
            margin-bottom: 6px;
        }

        .principle p {
            font-size: 0.9rem;
            color: var(--slate);
        }

        /* CTA band */
        .cta-band {
            background: linear-gradient(135deg, var(--gold-dim), transparent);
            border-top: 1px solid var(--gold-border);
            border-bottom: 1px solid var(--gold-border);
            text-align: center;
            padding: 80px 24px;
        }

        .cta-band h2 {
            font-size: clamp(1.75rem, 3vw, 2.25rem);
            margin-bottom: 12px;
        }

        .cta-band p {
            color: var(--slate);
            max-width: 480px;
            margin: 0 auto 32px;
        }

        /* Footer */
        footer {
            padding: 64px 0 40px;
            border-top: 1px solid var(--card-border);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 48px;
        }

        .footer-brand .logo {
            margin-bottom: 16px;
        }

        .footer-brand p {
            color: var(--slate);
            font-size: 0.9rem;
            max-width: 280px;
        }

        .footer-col h5 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--slate);
            margin-bottom: 16px;
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col li {
            margin-bottom: 10px;
        }

        .footer-col a {
            font-size: 0.9rem;
            color: var(--ivory);
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .footer-col a:hover {
            opacity: 1;
            color: var(--gold);
        }

        .footer-bottom {
            padding-top: 32px;
            border-top: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 0.8rem;
            color: var(--slate);
        }

        .disclaimer {
            max-width: 640px;
            font-size: 0.75rem;
            color: var(--slate);
            opacity: 0.8;
            line-height: 1.5;
        }

        .logo-img {
            max-width: 60px;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .nav-links {
                display: none;
            }
            .menu-toggle {
                display: block;
            }
            .concept-grid,
            .categories-grid,
            .process-steps,
            .feature-row,
            .principles-grid,
            .footer-grid {
                grid-template-columns: 1fr;
            }
            .feature-row.reverse {
                direction: ltr;
            }
            .org-row {
                flex-direction: column;
                align-items: center;
            }
        }

        @media (max-width: 600px) {
            .section {
                padding: 72px 0;
            }
            .portfolio-mock {
                padding: 28px 20px;
            }
            .hero-actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav id="nav">
        <div class="container nav-inner">
            <a href="#" class="logo">
                <img src="{{ asset('images/v5yuA.png') }}" alt="Saturn Logo" class="logo-img">
                <img src="{{ asset('images/text.svg') }}" alt="" style="max-width: 150px;">
            </a>
            <ul class="nav-links">
                <li><a href="#concept">Concept</a></li>
                <li><a href="#structure">Structure</a></li>
                <li><a href="#categories">Investments</a></li>
                <li><a href="#process">Process</a></li>
                <li><a href="#portfolio">Portfolio</a></li>
                <li>
                    @if (Route::has('login'))
                        <div>
                            @auth
                                <a href="{{ url('/dashboard') }}" class="nav-cta">Dashboard</a>
                            @else
                                <a href="{{ route('register') }}" class="nav-cta">Get Started</a>
                            @endauth
                        </div>
                    @endif
                </li>
            </ul>
            <button class="menu-toggle" aria-label="Menu">☰</button>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>SA<span>TU</span>RN</h1>
                <p class="hero-tagline">Own a share of what the world runs on.</p>
                <p style="color: var(--slate); max-width: 520px; margin-bottom: 40px; font-size: 1.05rem;">
                    Saturn gives everyday investors access to carefully selected real-world assets — businesses and infrastructure that produce, operate and earn in the physical economy. See the asset. Understand the numbers. Decide for yourself.
                </p>
                <div class="hero-actions">
                    <a href="#concept" class="btn btn-primary">Explore Saturn</a>
                    <a href="#structure" class="btn btn-secondary">How it works</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Concept -->
    <section class="section" id="concept">
        <div class="container">
            <span class="section-label">The idea</span>
            <h2 class="section-title">A different way to own real assets.</h2>
            <p class="section-lead">
                Some of the world’s most productive assets are difficult to own directly. They require large amounts of capital, specialist knowledge and years of commitment. Saturn brings the structure around them so investors can participate through clearly defined investments — without having to run the orchard, maintain the turbines or manage the property themselves.
            </p>

            <div class="concept-grid">
                <div>
                    <div class="concept-card" style="margin-bottom: 24px;">
                        <h3>Know what you own</h3>
                        <p>Your investment is tied to a defined legal and economic interest in a specific project. Your account shows the asset, your ownership interest, the cash it generates, its valuation methodology and the risks involved. The technology stays in the background. The ownership is the point.</p>
                    </div>
                    <div class="concept-card">
                        <h3>One project. One structure.</h3>
                        <p>Each project is structured separately where appropriate, so its assets, liabilities, cash flows and investor interests can be clearly identified. Saturn is the platform around the investments — not a black box holding everything together.</p>
                    </div>
                </div>
                <div>
                    <ul class="concept-list">
                        <li><span class="check">✓</span> Access assets without buying the whole asset</li>
                        <li><span class="check">✓</span> Participate in the economics of productive assets</li>
                        <li><span class="check">✓</span> Clear legal structure for each project</li>
                        <li><span class="check">✓</span> Independent analysis before an asset reaches investors</li>
                        <li><span class="check">✓</span> See the downside, not just the headline return</li>
                        <li><span class="check">✓</span> A path to liquidity where the legal and market conditions support it</li>
                        <li><span class="check">✓</span> Capital is at risk. Saturn never sells certainty.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Structure -->
    <section class="section structure" id="structure">
        <div class="container">
            <span class="section-label">Built around the asset</span>
            <h2 class="section-title">The platform is simple. The structure behind it is not.</h2>
            <p class="section-lead">
                Saturn separates the technology, investment platform, project ownership and day-to-day operations. That lets professional operators do what they are good at, while Saturn focuses on sourcing, structuring, oversight and investor experience.
            </p>

            <div class="org-chart">
                <div class="org-node primary">
                    <div class="label">Holding</div>
                    <div class="name">SATURN GROUP</div>
                </div>
                <div class="org-connector"></div>
                <div class="org-row">
                    <div class="org-branch">
                        <div class="org-node">
                            <div class="label">Technology + UX</div>
                            <div class="name">Saturn Saturn</div>
                        </div>
                    </div>
                    <div class="org-branch">
                        <div class="org-node">
                            <div class="label">Structuring</div>
                            <div class="name">Saturn Capital</div>
                        </div>
                    </div>
                </div>
                <div class="org-connector"></div>
                <div class="org-node primary">
                    <div class="label">Company entity per project</div>
                    <div class="name">ASSET SPV / PROJECT</div>
                </div>
                <div class="org-connector"></div>
                <div class="org-row">
                    <div class="org-branch">
                        <div class="org-node">
                            <div class="label">Owns the asset</div>
                            <div class="name">Asset</div>
                            <div style="font-size:0.8rem;color:var(--slate);margin-top:4px;">Orchard · Farm · Wind · RE</div>
                        </div>
                    </div>
                    <div class="org-branch">
                        <div class="org-node">
                            <div class="label">Operates it</div>
                            <div class="name">Operator</div>
                            <div style="font-size:0.8rem;color:var(--slate);margin-top:4px;">Farmer · Energy co.</div>
                        </div>
                    </div>
                </div>
                <div class="org-connector"></div>
                <div class="org-node">
                    <div class="label">Generated by operations</div>
                    <div class="name">CASH FLOW</div>
                </div>
                <div class="org-connector"></div>
                <div class="org-node primary">
                    <div class="label">Receive distributions</div>
                    <div class="name">INVESTORS</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Investments -->
    <section class="section" id="categories">
        <div class="container">
            <span class="section-label">Choose your exposure</span>
            <h2 class="section-title">Different assets. Different reasons to invest.</h2>
            <p class="section-lead">
                Not every asset should behave the same way. Some are built around established cash flow. Others require capital to grow. Others carry more uncertainty in exchange for greater potential. Saturn makes those differences visible.
            </p>

            <div class="categories-grid">
                <div class="cat-card">
                    <div class="cat-icon">📈</div>
                    <h3>Saturn Income</h3>
                    <p>Established assets with operating history and visible cash flow.</p>
                    <ul class="cat-examples">
                        <li>Wind & solar parks</li>
                        <li>Rented real estate</li>
                        <li>Infrastructure assets</li>
                    </ul>
                </div>
                <div class="cat-card">
                    <div class="cat-icon">🌱</div>
                    <h3>Saturn Growth</h3>
                    <p>Assets where growth, improvement or appreciation can drive the investment case.</p>
                    <ul class="cat-examples">
                        <li>Agricultural land</li>
                        <li>Young orchards</li>
                        <li>Real-estate developments</li>
                    </ul>
                </div>
                <div class="cat-card">
                    <div class="cat-icon">⚡</div>
                    <h3>Saturn Opportunity</h3>
                    <p>Earlier-stage or turnaround opportunities where uncertainty is materially higher.</p>
                    <ul class="cat-examples">
                        <li>Farms needing modernization</li>
                        <li>Energy projects in development</li>
                        <li>Land awaiting development</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Process -->
    <section class="section" id="process" style="background: rgba(0,0,0,0.2);">
        <div class="container">
            <span class="section-label">Before you see it</span>
            <h2 class="section-title">Most assets never make it onto Saturn.</h2>
            <p class="section-lead">
                An opportunity has to survive financial, legal, operational and market scrutiny before it becomes an investment opportunity. The objective is not to fill the platform with deals. It is to find a small number worth presenting.
            </p>

            <div class="process-steps">
                <div class="step">
                    <h4>01 — Find</h4>
                    <p>We source assets directly from owners, operators, developers and specialist networks.</p>
                </div>
                <div class="step">
                    <h4>02 — Screen</h4>
                    <p>We test the economics: revenue, costs, CAPEX, valuation, market conditions, operator quality and downside scenarios.</p>
                </div>
                <div class="step">
                    <h4>03 — Verify</h4>
                    <p>Company, financial, operational and environmental diligence. Material assumptions are challenged and valuations are independently assessed where appropriate.</p>
                </div>
                <div class="step">
                    <h4>04 — Decide</h4>
                    <p>Investment 04 — Decide (CEO, CFO, Risk, Company, sector specialist) votes Approve or Reject.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio example -->
    <section class="section" id="portfolio">
        <div class="container">
            <div class="feature-row">
                <div>
                    <span class="section-label">The investor view</span>
                    <h2 class="section-title">Know where your money is.</h2>
                    <p class="section-lead" style="margin-bottom: 24px;">
                        Saturn should never make an investment feel like a number on a screen. Your portfolio connects every position to the underlying asset — what it owns, how it performs, what it has paid out and what could go wrong.
                    </p>
                    <ul class="concept-list" style="max-width: 420px;">
                        <li><span class="check">✓</span> What you invested. What it is worth. What you have received.</li>
                        <li><span class="check">✓</span> How your capital is allocated</li>
                        <li><span class="check">✓</span> Cash-flow expectations, clearly labelled as estimates</li>
                        <li><span class="check">✓</span> Risk and liquidity at the asset level</li>
                    </ul>
                </div>
                <div class="portfolio-mock">
                    <div class="portfolio-header">
                        <div>
                            <div class="stat-label">Your Portfolio</div>
                            <div class="portfolio-total">€12,450</div>
                        </div>
                        <div style="text-align:right;">
                            <div class="portfolio-change">+€684 · +5.8%</div>
                            <div class="stat-label">earned</div>
                        </div>
                    </div>
                    <div class="portfolio-stats">
                        <div class="stat">
                            <div class="stat-label">Expected / year</div>
                            <div class="stat-value">€950</div>
                        </div>
                        <div class="stat">
                            <div class="stat-label">Assets</div>
                            <div class="stat-value">3</div>
                        </div>
                    </div>
                    <div style="font-size:0.75rem;color:var(--slate);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.06em;">My Assets</div>
                    <div class="asset-row">
                        <div class="asset-icon">🍎</div>
                        <div class="asset-info">
                            <div class="asset-name">Apple Orchard #04</div>
                            <div class="asset-meta">0.15% · Risk 3/5 · Low liquidity</div>
                        </div>
                        <div class="asset-amount">
                            €3,000
                            <div class="asset-yield">~€240/yr</div>
                        </div>
                    </div>
                    <div class="asset-row">
                        <div class="asset-icon">⚡</div>
                        <div class="asset-info">
                            <div class="asset-name">Wind Park #12</div>
                            <div class="asset-meta">0.08% · Risk 2/5 · Medium</div>
                        </div>
                        <div class="asset-amount">
                            €5,000
                            <div class="asset-yield">~€410/yr</div>
                        </div>
                    </div>
                    <div class="asset-row">
                        <div class="asset-icon">🏠</div>
                        <div class="asset-info">
                            <div class="asset-name">Residential #07</div>
                            <div class="asset-meta">0.22% · Risk 2/5 · Medium</div>
                        </div>
                        <div class="asset-amount">
                            €4,450
                            <div class="asset-yield">~€300/yr</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Risk & scenarios -->
            <div class="feature-row reverse" style="margin-top: 96px;">
                <div>
                    <span class="section-label">Nothing important hidden</span>
                    <h2 class="section-title">Returns are a scenario, not a promise.</h2>
                    <p class="section-lead" style="margin-bottom: 24px;">
                        A projected return is only useful when you understand what has to happen to achieve it. Saturn presents the assumptions behind the investment, including downside cases, liquidity constraints and the possibility of losing capital.
                    </p>
                    <p style="color:var(--slate);font-size:0.95rem;">
                        We will never build the brand around guaranteed returns or easy money. Investments carry risk. Some will perform well. Some will disappoint. The job is to make the decision and the risks clear.
                    </p>
                </div>
                <div class="feature-visual">
                    <div style="font-weight:700;margin-bottom:4px;">Apple Orchard #04</div>
                    <div style="font-size:0.85rem;color:var(--slate);margin-bottom:8px;">Romania · 120 ha · Risk 3/5</div>
                    <div class="risk-bar">
                        <div class="risk-segment active"></div>
                        <div class="risk-segment active"></div>
                        <div class="risk-segment active"></div>
                        <div class="risk-segment"></div>
                        <div class="risk-segment"></div>
                    </div>
                    <div class="scenario-grid">
                        <div class="scenario down">
                            <div class="scenario-label">Downside</div>
                            <div class="scenario-value">−8%</div>
                        </div>
                        <div class="scenario base">
                            <div class="scenario-label">Base</div>
                            <div class="scenario-value">+7.2%</div>
                        </div>
                        <div class="scenario up">
                            <div class="scenario-label">Upside</div>
                            <div class="scenario-value">+14.5%</div>
                        </div>
                    </div>
                    <div style="margin-top:20px;font-size:0.85rem;color:var(--slate);">
                        Target holding · 6.8 years · Min. €500 · Liquidity C
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Principles -->
    <section class="section" style="background: rgba(0,0,0,0.2);">
        <div class="container">
            <span class="section-label">What we believe</span>
            <h2 class="section-title">Trust is built into the structure.</h2>

            <div class="principles-grid">
                <div class="principle">
                    <div class="principle-num">01</div>
                    <div>
                        <h4>01 — Skin in the game</h4>
                        <p>Where appropriate and legally feasible, Saturn or the sponsor can invest alongside investors. Alignment should be visible, not just promised.</p>
                    </div>
                </div>
                <div class="principle">
                    <div class="principle-num">02</div>
                    <div>
                        <h4>02 — Aligned incentives</h4>
                        <p>Fees should be understandable, competitive and connected to the work and value created. The economics must make sense for investors as well as Saturn.</p>
                    </div>
                </div>
                <div class="principle">
                    <div class="principle-num">03</div>
                    <div>
                        <h4>03 — Independent valuation</h4>
                        <p>Estimated values are identified as estimates. Important assets are independently valued or reviewed at appropriate intervals.</p>
                    </div>
                </div>
                <div class="principle">
                    <div class="principle-num">04</div>
                    <div>
                        <h4>04 — Open the file</h4>
                        <p>Investors should be able to understand the investment case from the underlying documentation, not from a sales pitch.</p>
                    </div>
                </div>
                <div class="principle">
                    <div class="principle-num">05</div>
                    <div>
                        <h4>05 — Prepare for reality</h4>
                        <p>Good assets still face bad years. Reserves, insurance and contingency planning are part of underwriting — not emergency fixes.</p>
                    </div>
                </div>
                <div class="principle">
                    <div class="principle-num">06</div>
                    <div>
                        <h4>06 — Liquidity without fiction</h4>
                        <p>A marketplace does not magically make an illiquid asset liquid. Where a secondary market is legally available, Saturn can provide the infrastructure — while showing the actual price, volume and liquidity conditions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Money flow -->
    <section class="section">
        <div class="container" style="text-align:center;">
            <span class="section-label">The money flow</span>
            <h2 class="section-title" style="max-width:800px;margin:0 auto 24px;">
                Capital → asset → operations → cash flow → investor
            </h2>
            <p style="color:var(--slate);max-width:560px;margin:0 auto;font-size:1.1rem;">
                The technology is there to make ownership, reporting and transactions easier. It is not the investment. The investment is the underlying asset and the economic rights attached to it.
            </p>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-band" id="cta">
        <div class="container">
            <h2>Own something that does something.</h2>
            <p>Saturn is being built as a transparent platform for structured investment in productive real-world assets. Join early and follow the platform as the first opportunities take shape.</p>
            <a href="/preregister" class="btn btn-primary">Join Saturn</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="#" class="logo">
                        <img src="{{ asset('images/v5yuA.png') }}" alt="Saturn Logo" class="logo-img">
                        <img src="{{ asset('images/text.svg') }}" alt="" style="max-width: 150px;">
                    </a>
                    <p>A platform for structured access to productive real-world assets — built around transparency, disciplined underwriting and long-term ownership.</p>
                </div>
                <div class="footer-col">
                    <h5>Saturn</h5>
                    <ul>
                        <li><a href="#concept">Concept</a></li>
                        <li><a href="#structure">Structure</a></li>
                        <li><a href="#categories">Investments</a></li>
                        <li><a href="#process">Process</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>Investments</h5>
                    <ul>
                        <li><a href="#categories">Income</a></li>
                        <li><a href="#categories">Growth</a></li>
                        <li><a href="#categories">Opportunity</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>Company</h5>
                    <ul>
                        <li><a href="#">Risk & disclosures</a></li>
                        <li><a href="#">Terms</a></li>
                        <li><a href="#">Privacy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="disclaimer">
                    This is a business architecture concept, not legal advice. Before accepting capital from the public, the instrument structure, SPVs, platform, profit distribution and any secondary market must be properly framed under the applicable law of the launch jurisdiction (including ECSPR / ESMA requirements where relevant). Capital is at risk.
                </div>
                <div>© 2026 Saturn Group</div>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        const nav = document.getElementById('nav');
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 40);
        });

        // Simple mobile menu (toggle visibility of links)
        document.querySelector('.menu-toggle').addEventListener('click', () => {
            const links = document.querySelector('.nav-links');
            const isVisible = links.style.display === 'flex';
            links.style.display = isVisible ? 'none' : 'flex';
            links.style.flexDirection = 'column';
            links.style.position = 'absolute';
            links.style.top = '100%';
            links.style.left = '0';
            links.style.right = '0';
            links.style.background = 'var(--midnight)';
            links.style.padding = '24px';
            links.style.borderBottom = '1px solid var(--card-border)';
            links.style.gap = '16px';
        });
    </script>
</body>
</html>
