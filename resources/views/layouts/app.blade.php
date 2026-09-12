<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — Saturn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/tab.png') }}">
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
            --green-dim: rgba(74, 222, 128, 0.12);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Manrope', system-ui, sans-serif;
            background: var(--midnight);
            color: var(--ivory);
            line-height: 1.55;
        }

        a { color: inherit; text-decoration: none; }
        button { font-family: inherit; }

        .icon { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; vertical-align: middle; flex-shrink: 0; }

        /* ---------- App shell ---------- */
        .app-shell { display: flex; min-height: 100vh; }

        .sidebar {
            width: 264px;
            flex-shrink: 0;
            border-right: 1px solid var(--card-border);
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 8px 24px;
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
        }

        .nav-group { margin-bottom: 18px; }

        .nav-group-label {
            font-size: 1.08rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ivory);
            padding: 0 12px;
            margin-bottom: 6px;
            font-weight: 800;
        }

        .nav-link, .nav-sublink {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 0.88rem;
            color: var(--ivory);
            font-weight: 600;
        }

        .nav-link:hover, .nav-sublink:hover { background: var(--card-bg); }

        .nav-link.active { background: var(--gold-dim); color: var(--gold); }

        .nav-sublink { font-weight: 500; font-size: 0.83rem; color: var(--slate); padding-left: 14px; }
        .nav-sublink.active { color: var(--gold); background: var(--gold-dim); }

        .sidebar-footer { margin-top: auto; border-top: 1px solid var(--card-border); padding-top: 16px; }

        .user-chip { display: flex; align-items: center; gap: 10px; padding: 6px 10px; }

        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--gold-dim); color: var(--gold);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem; flex-shrink: 0;
        }

        .user-chip .meta { overflow: hidden; }
        .user-chip .name { font-size: 0.85rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-chip .email { font-size: 0.73rem; color: var(--slate); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .logout-form { margin-top: 10px; }
        .logout-btn {
            width: 100%; background: transparent; border: 1px solid var(--card-border);
            color: var(--slate); padding: 9px; border-radius: 8px; font-size: 0.8rem;
            font-weight: 600; cursor: pointer; transition: border-color .15s, color .15s;
        }
        .logout-btn:hover { border-color: var(--red); color: var(--red); }

        .main-col { flex: 1; min-width: 0; display: flex; flex-direction: column; }

        .topbar {
            height: 72px; flex-shrink: 0; display: flex; align-items: center;
            justify-content: space-between; padding: 0 32px;
            border-bottom: 1px solid var(--card-border);
            position: sticky; top: 0; background: rgba(11, 18, 32, 0.85);
            backdrop-filter: blur(12px); z-index: 10;
        }

        .topbar h1 { font-size: 1.1rem; font-weight: 700; }

        .topbar-search { position: relative; display: flex; align-items: center; }
        .topbar-search .icon { position: absolute; left: 12px; color: var(--slate); }
        .topbar-search input {
            background: rgba(0, 0, 0, 0.25); border: 1px solid var(--card-border);
            border-radius: 8px; padding: 8px 12px 8px 36px; color: var(--ivory);
            font-family: inherit; font-size: 0.85rem; width: 220px;
        }
        .topbar-search input::placeholder { color: var(--slate); }

        .topbar-actions { display: flex; align-items: center; gap: 14px; }

        .icon-btn-round {
            width: 36px; height: 36px; border-radius: 9px; border: 1px solid var(--card-border);
            background: transparent; display: flex; align-items: center; justify-content: center;
            color: var(--slate); cursor: pointer; transition: color .15s, border-color .15s;
        }
        .icon-btn-round:hover { color: var(--ivory); border-color: var(--slate); }

        .content { padding: 32px; max-width: 1200px; width: 100%; margin: 0 auto; flex: 1; }

        /* ---------- Shared components ---------- */
        .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 16px; padding: 24px; }

        .grid { display: grid; gap: 20px; }
        .grid-2 { grid-template-columns: 1fr 1fr; }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; gap: 12px; flex-wrap: wrap; }
        .section-title { font-size: 1.02rem; font-weight: 700; margin-bottom: 16px; }
        .card-header .section-title { margin-bottom: 0; }
        .link-muted { font-size: 0.82rem; color: var(--gold); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }

        .stat-label { font-size: 0.76rem; color: var(--slate); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-value { font-size: 1.35rem; font-weight: 700; font-variant-numeric: tabular-nums; }

        .badge { display: inline-flex; align-items: center; gap: 4px; font-size: 0.76rem; font-weight: 700; padding: 3px 9px; border-radius: 100px; }
        .badge.positive { background: var(--green-dim); color: var(--green); }
        .badge.negative { background: var(--red-dim); color: var(--red); }
        .badge.neutral { background: var(--gold-dim); color: var(--gold); }
        .badge.muted { background: var(--card-bg); color: var(--slate); border: 1px solid var(--card-border); }

        .positive { color: var(--green); }
        .negative { color: var(--red); }
        .muted { color: var(--slate); }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--slate); padding: 10px 12px; border-bottom: 1px solid var(--card-border); }
        td { padding: 14px 12px; border-bottom: 1px solid var(--card-border); font-size: 0.88rem; }
        tr:last-child td { border-bottom: none; }
        tr.row-link { cursor: pointer; transition: background .15s; }
        tr.row-link:hover { background: var(--card-bg); }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 11px 20px; border-radius: 9px; font-size: 0.87rem; font-weight: 700;
            cursor: pointer; border: none; transition: transform .15s, box-shadow .15s, border-color .15s, color .15s;
        }
        .btn-gold { background: var(--gold); color: var(--midnight); }
        .btn-gold:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(198, 161, 91, 0.3); }
        .btn-outline { background: transparent; color: var(--ivory); border: 1px solid var(--card-border); }
        .btn-outline:hover { border-color: var(--slate); }
        .btn-danger-outline { background: transparent; color: var(--red); border: 1px solid rgba(248, 113, 113, 0.3); }
        .btn-danger-outline:hover { background: var(--red-dim); }
        .btn-sm { padding: 8px 14px; font-size: 0.78rem; }
        .btn-block { width: 100%; }

        .tabs { display: flex; gap: 4px; border-bottom: 1px solid var(--card-border); margin-bottom: 24px; overflow-x: auto; }
        .tab-btn { padding: 12px 16px; font-size: 0.86rem; font-weight: 600; color: var(--slate); background: none; border: none; cursor: pointer; border-bottom: 2px solid transparent; white-space: nowrap; }
        .tab-btn.active { color: var(--gold); border-bottom-color: var(--gold); }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        .list-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--card-border); }
        .list-row:last-child { border-bottom: none; }
        .list-row .primary { font-weight: 700; font-size: 0.9rem; }
        .list-row .secondary { font-size: 0.78rem; color: var(--slate); margin-top: 2px; }
        .list-row .value { font-weight: 700; font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }

        .progress-track { height: 6px; border-radius: 3px; background: var(--card-border); overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; background: var(--gold); border-radius: 3px; }

        .allocation-row { margin-bottom: 14px; }
        .allocation-row:last-child { margin-bottom: 0; }
        .allocation-row .top { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 6px; }
        .allocation-bar { height: 8px; border-radius: 4px; background: var(--card-border); overflow: hidden; }
        .allocation-fill { height: 100%; background: var(--gold); }

        .filter-bar { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; align-items: center; }
        .filter-group-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--slate); margin-right: 2px; }
        .filter-chip, .chip {
            padding: 7px 14px; border-radius: 100px; border: 1px solid var(--card-border);
            background: transparent; color: var(--slate); font-size: 0.8rem; font-weight: 600;
            cursor: pointer; transition: border-color .15s, color .15s, background .15s;
        }
        .filter-chip.active, .chip.active { border-color: var(--gold-border); color: var(--gold); background: var(--gold-dim); }

        .watch-toggle { background: none; border: none; cursor: pointer; padding: 4px; color: var(--slate); }
        .watch-toggle .icon { fill: none; stroke: var(--slate); }
        .watch-toggle.is-active .icon { fill: var(--gold); stroke: var(--gold); }

        /* Chart */
        .chart-card .chart-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; flex-wrap: wrap; gap: 12px; }
        .chart-svg { width: 100%; height: auto; display: block; margin-top: 12px; }
        .chart-svg [data-range] { display: none; }
        .chart-svg [data-range].range-active { display: block; }

        .disclaimer-box { font-size: 0.78rem; color: var(--slate); background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 10px; padding: 12px 14px; margin-top: 16px; }

        /* ---------- Mobile ---------- */
        .mobile-nav { display: none; }

        @media (max-width: 980px) {
            .sidebar { display: none; }
            .content { padding: 20px 16px 92px; }
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: 1fr 1fr; }
            .grid-2 { grid-template-columns: 1fr; }
            .topbar { padding: 0 16px; }
            .topbar-search { display: none; }
            .mobile-nav {
                display: flex; position: fixed; bottom: 0; left: 0; right: 0; height: 64px;
                background: rgba(11, 18, 32, 0.95); backdrop-filter: blur(16px);
                border-top: 1px solid var(--card-border); z-index: 20;
            }
            .mobile-nav a { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; color: var(--slate); font-size: 0.66rem; font-weight: 600; }
            .mobile-nav a.active { color: var(--gold); }
        }

        @media (max-width: 560px) {
            .grid-4, .grid-3 { grid-template-columns: 1fr 1fr; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Icon sprite: reused everywhere via <svg class="icon"><use href="#icon-x"/></svg> -->
    <svg style="display:none" aria-hidden="true">
        <symbol id="icon-home" viewBox="0 0 24 24"><path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9a1 1 0 0 0 1 1h3v-6h4v6h3a1 1 0 0 0 1-1v-9"/></symbol>
        <symbol id="icon-portfolio" viewBox="0 0 24 24"><path d="M4 20V4"/><path d="M4 20h16"/><rect x="7" y="12" width="3" height="8"/><rect x="12" y="8" width="3" height="12"/><rect x="17" y="15" width="3" height="5"/></symbol>
        <symbol id="icon-market" viewBox="0 0 24 24"><path d="M6 8h12l-1.2 11a1 1 0 0 1-1 .9H8.2a1 1 0 0 1-1-.9L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></symbol>
        <symbol id="icon-activity" viewBox="0 0 24 24"><path d="M6 3h12v18l-2.5-1.8L13 21l-2.5-1.8L8 21l-2-1.8V3z"/><path d="M9 8h6M9 12h6M9 16h4"/></symbol>
        <symbol id="icon-account" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 3.2-6.5 7-6.5s7 2.5 7 6.5"/></symbol>
        <symbol id="icon-bell" viewBox="0 0 24 24"><path d="M6.5 9a5.5 5.5 0 0 1 11 0c0 4.5 1.8 5.8 1.8 5.8H4.7S6.5 13.5 6.5 9z"/><path d="M10 18a2 2 0 0 0 4 0"/></symbol>
        <symbol id="icon-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.8-4.8"/></symbol>
        <symbol id="icon-heart" viewBox="0 0 24 24"><path d="M12 20.2s-7.2-4.4-9.6-8.6C.7 8 3.2 4.6 6.6 4.6c2 0 3.3 1 5.4 3 2.1-2 3.4-3 5.4-3 3.4 0 5.9 3.4 4.2 7-2.4 4.2-9.6 8.6-9.6 8.6z"/></symbol>
        <symbol id="icon-chevron-right" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></symbol>
        <symbol id="icon-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
        <symbol id="icon-minus" viewBox="0 0 24 24"><path d="M5 12h14"/></symbol>
        <symbol id="icon-arrow-up-right" viewBox="0 0 24 24"><path d="M7 17 17 7"/><path d="M9 7h8v8"/></symbol>
        <symbol id="icon-arrow-down-right" viewBox="0 0 24 24"><path d="M7 7 17 17"/><path d="M11 17h6v-6"/></symbol>
        <symbol id="icon-close" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></symbol>
        <symbol id="icon-filter" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10M10 18h4"/></symbol>
        <symbol id="icon-arrow-left" viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></symbol>
    </svg>

    <div class="app-shell">
        <aside class="sidebar">
            <a href="/dashboard" class="sidebar-logo">
                <img src="{{ asset('images/v5yuA.png') }}" alt="Saturn" style="height: 26px;">
                <img src="{{ asset('images/text.svg') }}" alt="" style="max-width: 150px;">
            </a>

            <div class="nav-group">
                <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <svg class="icon"><use href="#icon-home"/></svg> Home
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-group-label">Portfolio</div>
                <a href="/portfolio#overview" class="nav-sublink">Overview</a>
                <a href="/portfolio#holdings" class="nav-sublink">Holdings</a>
                <a href="/portfolio#allocation" class="nav-sublink">Allocation</a>
                <a href="/portfolio#performance" class="nav-sublink">Performance</a>
            </div>

            <div class="nav-group">
                <div class="nav-group-label">Market</div>
                <a href="/market#discover" class="nav-sublink">Discover</a>
                <a href="/market#new-projects" class="nav-sublink">New Projects</a>
                <a href="/market#secondary" class="nav-sublink">Secondary Market</a>
                <a href="/market#watchlist" class="nav-sublink">Watchlist</a>
                <a href="/market#my-listings" class="nav-sublink">My Listings</a>
            </div>

            <div class="nav-group">
                <div class="nav-group-label">Income</div>
                <a href="/income#distributions" class="nav-sublink">Distributions</a>
                <a href="/income#cashflow" class="nav-sublink">Cash Flow</a>
            </div>

            <div class="nav-group">
                <div class="nav-group-label">Activity</div>
                <a href="/activity#transactions" class="nav-sublink">Transactions</a>
                <a href="/activity#orders" class="nav-sublink">Orders</a>
            </div>

            <div class="nav-group">
                <div class="nav-group-label">Account</div>
                <a href="/account#profile" class="nav-sublink">Profile</a>
                <a href="/account#verification" class="nav-sublink">Verification</a>
                <a href="/account#bank" class="nav-sublink">Bank</a>
                <a href="/account#security" class="nav-sublink">Security</a>
                <a href="/account#documents" class="nav-sublink">Documents</a>
            </div>

            <div class="sidebar-footer">
                <div class="user-chip">
                    <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <div class="meta">
                        <div class="name">{{ Auth::user()->name }}</div>
                        <div class="email">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-btn">Log out</button>
                </form>
            </div>
        </aside>

        <div class="main-col">
            <header class="topbar">
                <h1>@yield('title', 'Dashboard')</h1>
                <div class="topbar-actions">
                    <div class="topbar-search">
                        <svg class="icon"><use href="#icon-search"/></svg>
                        <input type="text" placeholder="Search assets…">
                    </div>
                    <button class="icon-btn-round" title="Notifications">
                        <svg class="icon"><use href="#icon-bell"/></svg>
                    </button>
                </div>
            </header>
            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>

    <nav class="mobile-nav">
        <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}"><svg class="icon"><use href="#icon-home"/></svg><span>Home</span></a>
        <a href="/market" class="{{ request()->is('market') ? 'active' : '' }}"><svg class="icon"><use href="#icon-market"/></svg><span>Market</span></a>
        <a href="/portfolio" class="{{ request()->is('portfolio') ? 'active' : '' }}"><svg class="icon"><use href="#icon-portfolio"/></svg><span>Portfolio</span></a>
        <a href="/activity" class="{{ request()->is('activity') ? 'active' : '' }}"><svg class="icon"><use href="#icon-activity"/></svg><span>Activity</span></a>
        <a href="/account" class="{{ request()->is('account') ? 'active' : '' }}"><svg class="icon"><use href="#icon-account"/></svg><span>Profile</span></a>
    </nav>

    <script>
    // Tab Controller for pages with `.tabs[data-panels="#id"]`
    function initTabs() {
        document.querySelectorAll('.tabs[data-panels]').forEach((tabGroup) => {
            const buttons = tabGroup.querySelectorAll('.tab-btn');
            const panelContainer = document.querySelector(tabGroup.dataset.panels);
            if (!panelContainer) return;
            const panels = panelContainer.querySelectorAll('.tab-panel');

            function activate(tabId, updateHash = true) {
                buttons.forEach(b => b.classList.toggle('active', b.dataset.tab === tabId));
                panels.forEach(p => p.classList.toggle('active', p.id === tabId));
                if (updateHash && window.location.hash !== '#' + tabId) {
                    history.pushState(null, '', '#' + tabId);
                }
            }

            buttons.forEach(btn => btn.addEventListener('click', () => activate(btn.dataset.tab)));

            // Switch tabs dynamically when the URL hash changes
            function handleHashChange() {
                const hash = window.location.hash.replace('#', '');
                const match = Array.from(buttons).find(b => b.dataset.tab === hash);
                if (match) {
                    activate(hash, false);
                } else if (buttons[0]) {
                    activate(buttons[0].dataset.tab, false);
                }
            }

            window.addEventListener('hashchange', handleHashChange);
            handleHashChange(); // Run on initial page load
        });
    }

    // Initialize tabs on DOM content load
    document.addEventListener('DOMContentLoaded', () => {
        initTabs();

        // Chart controller
        document.querySelectorAll('.chart-card').forEach((card) => {
            const chips = card.querySelectorAll('.chip[data-range]');
            const svg = card.querySelector('.chart-svg');
            if (!svg || chips.length === 0) return;

            function setRange(range) {
                chips.forEach(c => c.classList.toggle('active', c.dataset.range === range));
                svg.querySelectorAll('[data-range]').forEach(el => el.classList.toggle('range-active', el.dataset.range === range));
            }

            chips.forEach(chip => chip.addEventListener('click', () => setRange(chip.dataset.range)));
            const initial = card.querySelector('.chip.active')?.dataset.range || chips[0]?.dataset.range;
            if (initial) setRange(initial);
        });

        // Filter chips and watchlist toggles
        document.querySelectorAll('.filter-chip').forEach((btn) => {
            btn.addEventListener('click', () => btn.classList.toggle('active'));
        });
        document.querySelectorAll('.watch-toggle').forEach((btn) => {
            btn.addEventListener('click', () => btn.classList.toggle('is-active'));
        });
    });
</script>
    @stack('scripts')
</body>
</html>