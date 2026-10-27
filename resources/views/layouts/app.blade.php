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

    {{-- Apply the saved sidebar state before first paint, so there is no flash. --}}
    <script>
        try { if (localStorage.getItem('saturn.sidebar') === 'collapsed') document.documentElement.classList.add('sb-collapsed'); } catch (e) {}
    </script>

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
            --sb-w: 268px;
            --ease: cubic-bezier(.4, 0, .2, 1);
        }
        html.sb-collapsed { --sb-w: 76px; }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }

        body {
            font-family: 'Manrope', system-ui, sans-serif;
            background: var(--midnight);
            color: var(--ivory);
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; text-decoration: none; }
        button { font-family: inherit; }
        ::selection { background: var(--gold-dim); color: var(--ivory); }
        :focus-visible { outline: 2px solid var(--gold-border); outline-offset: 2px; }

        .skip-link { position: absolute; left: -9999px; top: 8px; background: var(--gold); color: var(--midnight); padding: 8px 14px; border-radius: 8px; font-weight: 700; font-size: .82rem; z-index: 100; }
        .skip-link:focus { left: 8px; }

        .icon { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; vertical-align: middle; flex-shrink: 0; }


/* ---------- Pagination ---------- */

nav[role="navigation"] {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    width: 100%;
    padding: 16px 0;
    font-size: 0.82rem;
    color: var(--slate);
}

nav[role="navigation"] > div {
    display: flex;
    align-items: center;
    gap: 6px;
}

nav[role="navigation"] a,
nav[role="navigation"] span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    min-height: 36px;
    border-radius: 8px;
    background: transparent;
    color: var(--ivory);
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
}

nav[role="navigation"] a:hover {
    border-color: var(--gold-border);
    background: var(--gold-dim);
    color: var(--gold);
}

nav[role="navigation"] span[aria-current="page"] {
    border-color: var(--gold-border);
    background: var(--gold-dim);
    color: var(--gold);
}

nav[role="navigation"] span[aria-disabled="true"] {
    color: var(--slate);
    opacity: 0.5;
}

nav[role="navigation"] svg {
    width: 16px !important;
    height: 16px !important;
    max-width: 16px;
    max-height: 16px;
    flex-shrink: 0;
}

nav[role="navigation"] p {
    margin: 0;
    color: var(--slate);
    font-size: 0.8rem;
}

@media (max-width: 560px) {
    nav[role="navigation"] {
        justify-content: center;
    }

    nav[role="navigation"] > div {
        justify-content: center;
        flex-wrap: wrap;
    }
}


        /* ---------- App shell ---------- */
        .app-shell { display: flex; min-height: 100vh; }

        .sidebar {
            width: var(--sb-w);
            flex-shrink: 0;
            border-right: 1px solid var(--card-border);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 30;
            background: var(--midnight);
            transition: width .22s var(--ease);
        }

        .sidebar-logo {
            height: 72px; flex-shrink: 0;
            display: flex; align-items: center; gap: 12px;
            padding: 0 22px;
            border-bottom: 1px solid var(--card-border);
            overflow: hidden;
        }
        .sidebar-logo img:first-child { height: 34px; width: auto; flex-shrink: 0; }
        .sidebar-logo img.logo-text { max-width: 140px; height: auto; }

        .sidebar-nav { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 20px 14px; scrollbar-width: thin; scrollbar-color: var(--card-border) transparent; }

        .nav-section { margin-bottom: 4px; }
        .nav-row { display: flex; align-items: center; position: relative; }

        .nav-link {
            position: relative;
            flex: 1; min-width: 0;
            display: flex; align-items: center; gap: 14px;
            height: 42px; padding: 0 14px;
            border-radius: 10px;
            font-size: .88rem; font-weight: 600;
            color: var(--slate);
            white-space: nowrap;
            transition: background .15s, color .15s;
        }
        .nav-link .icon { width: 19px; height: 19px; }
        .nav-link:hover { background: var(--card-bg); color: var(--ivory); }
        .nav-link.active { color: var(--gold); background: var(--gold-dim); }
        .nav-link.active::before { content: ''; position: absolute; left: -14px; top: 10px; bottom: 10px; width: 3px; border-radius: 0 3px 3px 0; background: var(--gold); }

        .nav-chev {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            width: 30px; height: 30px; border: 0; background: transparent; color: var(--slate);
            border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: color .15s, background .15s;
        }
        .nav-chev:hover { color: var(--ivory); background: var(--card-bg); }
        .nav-chev .icon { width: 15px; height: 15px; transition: transform .2s var(--ease); }
        .nav-section.open .nav-chev .icon { transform: rotate(180deg); }

        .nav-subs { display: none; margin: 2px 0 8px 27px; padding-left: 14px; border-left: 1px solid var(--card-border); }
        .nav-section.open .nav-subs { display: block; }
        .nav-sublink {
            display: block; padding: 7px 12px; border-radius: 8px;
            font-size: .82rem; font-weight: 500; color: var(--slate);
            transition: color .15s, background .15s;
        }
        .nav-sublink:hover { color: var(--ivory); background: var(--card-bg); }
        .nav-sublink.active { color: var(--gold); font-weight: 600; }

        .sidebar-footer { flex-shrink: 0; border-top: 1px solid var(--card-border); padding: 16px 14px; }
        .user-chip { display: flex; align-items: center; gap: 12px; padding: 4px 6px; position: relative; }
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--gold-dim); color: var(--gold);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem; flex-shrink: 0;
        }
        .user-chip .meta { overflow: hidden; min-width: 0; }
        .user-chip .name { font-size: .85rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-chip .email { font-size: .73rem; color: var(--slate); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .logout-form { margin-top: 12px; }
        .logout-btn {
            position: relative;
            width: 100%; height: 40px; display: flex; align-items: center; justify-content: center; gap: 10px;
            background: transparent; border: 1px solid var(--card-border);
            color: var(--slate); border-radius: 10px; font-size: .82rem;
            font-weight: 600; cursor: pointer; transition: border-color .15s, color .15s, background .15s;
        }
        .logout-btn .icon { width: 16px; height: 16px; }
        .logout-btn:hover { border-color: rgba(248,113,113,.4); color: var(--red); background: var(--red-dim); }

        /* Collapsed sidebar */
html.sb-collapsed .sidebar-nav {
    overflow: visible;
    padding-left: 12px;
    padding-right: 12px;
}

html.sb-collapsed .sidebar-logo {
    justify-content: center;
    padding: 0;
}

html.sb-collapsed .logo-text,
html.sb-collapsed .nav-link .label,
html.sb-collapsed .nav-chev,
html.sb-collapsed .nav-subs,
html.sb-collapsed .user-chip .meta,
html.sb-collapsed .logout-btn .label {
    display: none;
}

/* Critical fix: hide submenus even when their section is open. */
html.sb-collapsed .nav-section.open .nav-subs {
    display: none;
}

html.sb-collapsed .nav-link {
    justify-content: center;
    padding: 0;
}

html.sb-collapsed .nav-link.active::before {
    left: -12px;
}

html.sb-collapsed .user-chip {
    justify-content: center;
    padding: 4px 0;
}
        /* Tooltips (collapsed only) */
        [data-label]::after {
            content: attr(data-label);
            position: absolute; left: calc(100% + 14px); top: 50%; transform: translateY(-50%) translateX(-4px);
            background: #141c2e; color: var(--ivory); border: 1px solid var(--card-border);
            padding: 6px 10px; border-radius: 8px; font-size: .78rem; font-weight: 600; white-space: nowrap;
            opacity: 0; pointer-events: none; transition: opacity .12s, transform .12s; display: none; z-index: 50;
        }
        html.sb-collapsed [data-label]::after { display: block; }
        html.sb-collapsed [data-label]:hover::after,
        html.sb-collapsed [data-label]:focus-visible::after { opacity: 1; transform: translateY(-50%); }

        .main-col { flex: 1; min-width: 0; display: flex; flex-direction: column; }

        .topbar {
            height: 72px; flex-shrink: 0; display: flex; align-items: center;
            justify-content: space-between; padding: 0 32px; gap: 16px;
            border-bottom: 1px solid var(--card-border);
            position: sticky; top: 0; background: rgba(11, 18, 32, 0.85);
            backdrop-filter: blur(12px); z-index: 10;
        }
        .topbar-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
        .topbar h1 { font-size: 1.05rem; font-weight: 700; letter-spacing: -.01em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .sb-toggle {
            width: 36px; height: 36px; border-radius: 9px; border: 1px solid var(--card-border);
            background: transparent; color: var(--slate); cursor: pointer;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            transition: color .15s, border-color .15s;
        }
        .sb-toggle:hover { color: var(--ivory); border-color: var(--slate); }

        .topbar-search { position: relative; display: flex; align-items: center; }
        .topbar-search .icon { position: absolute; left: 12px; color: var(--slate); pointer-events: none; }
        .topbar-search input {
            background: rgba(0, 0, 0, 0.25); border: 1px solid var(--card-border);
            border-radius: 9px; padding: 9px 12px 9px 36px; color: var(--ivory);
            font-family: inherit; font-size: .85rem; width: 240px; outline: none; transition: border-color .15s, width .2s var(--ease);
        }
        .topbar-search input:focus { border-color: var(--gold-border); width: 300px; }
        .topbar-search input::placeholder { color: var(--slate); }

        .topbar-actions { display: flex; align-items: center; gap: 12px; }

        .icon-btn-round {
            width: 36px; height: 36px; border-radius: 9px; border: 1px solid var(--card-border);
            background: transparent; display: flex; align-items: center; justify-content: center;
            color: var(--slate); cursor: pointer; transition: color .15s, border-color .15s;
        }
        .icon-btn-round:hover { color: var(--ivory); border-color: var(--slate); }

        .content { padding: 36px 40px; max-width: 1240px; width: 100%; margin: 0 auto; flex: 1; }

        /* ---------- Shared components ---------- */
        .card { background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 14px; padding: 24px; }

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
            cursor: pointer; border: none; transition: transform .15s, box-shadow .15s, border-color .15s, color .15s, background .15s;
        }
        .btn-gold { background: var(--gold); color: var(--midnight); }
        .btn-gold:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(198, 161, 91, 0.3); }
        .btn-outline { background: transparent; color: var(--ivory); border: 1px solid var(--card-border); }
        .btn-outline:hover { border-color: var(--slate); }
        .btn-danger-outline { background: transparent; color: var(--red); border: 1px solid rgba(248, 113, 113, 0.3); }
        .btn-danger-outline:hover { background: var(--red-dim); }
        .btn-sm { padding: 8px 14px; font-size: 0.78rem; }
        .btn-block { width: 100%; }
        .btn:disabled { opacity: .45; cursor: not-allowed; }

        .tabs { display: flex; gap: 4px; border-bottom: 1px solid var(--card-border); margin-bottom: 24px; overflow-x: auto; scrollbar-width: none; }
        .tabs::-webkit-scrollbar { display: none; }
        .tab-btn { padding: 12px 16px; font-size: 0.86rem; font-weight: 600; color: var(--slate); background: none; border: none; cursor: pointer; border-bottom: 2px solid transparent; white-space: nowrap; transition: color .15s; }
        .tab-btn:hover { color: var(--ivory); }
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

        /* Chart (legacy pages) */
        .chart-card .chart-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; flex-wrap: wrap; gap: 12px; }
        .chart-svg { width: 100%; height: auto; display: block; margin-top: 12px; }
        .chart-svg [data-range] { display: none; }
        .chart-svg [data-range].range-active { display: block; }

        .disclaimer-box { font-size: 0.78rem; color: var(--slate); background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 10px; padding: 12px 14px; margin-top: 16px; }

        /* ---------- Mobile ---------- */
        .mobile-nav { display: none; }

        @media (max-width: 980px) {
            .sidebar { display: none; }
            .sb-toggle { display: none; }
            .content { padding: 22px 16px 96px; }
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: 1fr 1fr; }
            .grid-2 { grid-template-columns: 1fr; }
            .topbar { padding: 0 16px; height: 60px; }
            .topbar-search { display: none; }
            .mobile-nav {
                display: flex; position: fixed; bottom: 0; left: 0; right: 0; height: 66px;
                padding-bottom: env(safe-area-inset-bottom);
                background: rgba(11, 18, 32, 0.95); backdrop-filter: blur(16px);
                border-top: 1px solid var(--card-border); z-index: 20;
            }
            .mobile-nav a { position: relative; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; color: var(--slate); font-size: 0.62rem; font-weight: 600; transition: color .15s; }
            .mobile-nav a .icon { width: 20px; height: 20px; }
            .mobile-nav a.active { color: var(--gold); }
            .mobile-nav a.active::before { content: ''; position: absolute; top: 0; left: 28%; right: 28%; height: 2px; background: var(--gold); border-radius: 0 0 2px 2px; }
        }

        @media (max-width: 560px) {
            .grid-4, .grid-3 { grid-template-columns: 1fr 1fr; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; animation: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    // Navigation model, used by both the sidebar and the mobile bar.
    // Hardcoded: the route patterns under 'match' (asset/*, trade/*, secondary/*, listings/*, withdraw/*) are guesses.
    // Adjust them to your real URLs so the right section stays highlighted on those inner pages.
    $nav = [
        ['key' => 'home',      'label' => 'Home',      'short' => 'Home',      'icon' => 'home',      'href' => '/dashboard', 'match' => ['dashboard'], 'subs' => []],
        ['key' => 'portfolio', 'label' => 'Portfolio', 'short' => 'Portfolio', 'icon' => 'portfolio', 'href' => '/portfolio', 'match' => ['portfolio', 'portfolio/*', 'asset/*'],
            'subs' => ['overview' => 'Overview', 'holdings' => 'Holdings', 'allocation' => 'Allocation', 'performance' => 'Performance']],
        ['key' => 'market',    'label' => 'Market',    'short' => 'Market',    'icon' => 'market',    'href' => '/market',    'match' => ['market', 'market/*', 'trade/*', 'secondary/*', 'listings/*'],
            'subs' => ['discover' => 'Discover', 'secondary' => 'Secondary Market', 'watchlist' => 'Watchlist', 'my-listings' => 'My Listings']],
        ['key' => 'income',    'label' => 'Income',    'short' => 'Income',    'icon' => 'income',    'href' => '/income',    'match' => ['income', 'income/*'],
            'subs' => ['distributions' => 'Distributions', 'cashflow' => 'Cash Flow']],
        ['key' => 'activity',  'label' => 'Activity',  'short' => 'Activity',  'icon' => 'activity',  'href' => '/activity',  'match' => ['activity', 'activity/*'],
            'subs' => ['transactions' => 'Transactions', 'orders' => 'Orders']],
        ['key' => 'account',   'label' => 'Account',   'short' => 'Profile',   'icon' => 'account',   'href' => '/account',   'match' => ['account', 'account/*', 'withdraw', 'withdraw/*'],
            'subs' => ['profile' => 'Profile', 'verification' => 'Verification', 'bank' => 'Bank', 'security' => 'Security', 'documents' => 'Documents']],
    ];
    $nav = collect($nav)->map(function ($s) { $s['active'] = request()->is(...$s['match']); return $s; })->all();
@endphp

    <a href="#main" class="skip-link">Skip to content</a>

    <!-- Icon sprite: reused everywhere via <svg class="icon"><use href="#icon-x"/></svg> -->
    <svg style="display:none" aria-hidden="true">
        <symbol id="icon-home" viewBox="0 0 24 24"><path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9a1 1 0 0 0 1 1h3v-6h4v6h3a1 1 0 0 0 1-1v-9"/></symbol>
        <symbol id="icon-portfolio" viewBox="0 0 24 24"><path d="M4 20V4"/><path d="M4 20h16"/><rect x="7" y="12" width="3" height="8"/><rect x="12" y="8" width="3" height="12"/><rect x="17" y="15" width="3" height="5"/></symbol>
        <symbol id="icon-market" viewBox="0 0 24 24"><path d="M6 8h12l-1.2 11a1 1 0 0 1-1 .9H8.2a1 1 0 0 1-1-.9L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></symbol>
        <symbol id="icon-income" viewBox="0 0 24 24"><path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H18v3"/><path d="M4 7.5V17a2 2 0 0 0 2 2h12a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1H6.5A2.5 2.5 0 0 1 4 7.5z"/><circle cx="15.5" cy="13.5" r="1"/></symbol>
        <symbol id="icon-activity" viewBox="0 0 24 24"><path d="M6 3h12v18l-2.5-1.8L13 21l-2.5-1.8L8 21l-2-1.8V3z"/><path d="M9 8h6M9 12h6M9 16h4"/></symbol>
        <symbol id="icon-account" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 3.2-6.5 7-6.5s7 2.5 7 6.5"/></symbol>
        <symbol id="icon-bell" viewBox="0 0 24 24"><path d="M6.5 9a5.5 5.5 0 0 1 11 0c0 4.5 1.8 5.8 1.8 5.8H4.7S6.5 13.5 6.5 9z"/><path d="M10 18a2 2 0 0 0 4 0"/></symbol>
        <symbol id="icon-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.8-4.8"/></symbol>
        <symbol id="icon-heart" viewBox="0 0 24 24"><path d="M12 20.2s-7.2-4.4-9.6-8.6C.7 8 3.2 4.6 6.6 4.6c2 0 3.3 1 5.4 3 2.1-2 3.4-3 5.4-3 3.4 0 5.9 3.4 4.2 7-2.4 4.2-9.6 8.6-9.6 8.6z"/></symbol>
        <symbol id="icon-chevron-right" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></symbol>
        <symbol id="icon-chevron-down" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></symbol>
        <symbol id="icon-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
        <symbol id="icon-minus" viewBox="0 0 24 24"><path d="M5 12h14"/></symbol>
        <symbol id="icon-arrow-up-right" viewBox="0 0 24 24"><path d="M7 17 17 7"/><path d="M9 7h8v8"/></symbol>
        <symbol id="icon-arrow-down-right" viewBox="0 0 24 24"><path d="M7 7 17 17"/><path d="M11 17h6v-6"/></symbol>
        <symbol id="icon-close" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18"/></symbol>
        <symbol id="icon-filter" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10M10 18h4"/></symbol>
        <symbol id="icon-arrow-left" viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></symbol>
        <symbol id="icon-logout" viewBox="0 0 24 24"><path d="M10 5H6a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h4"/><path d="M15 8l4 4-4 4M19 12H9"/></symbol>
        <symbol id="icon-sidebar" viewBox="0 0 24 24"><rect x="4" y="5" width="16" height="14" rx="2"/><path d="M9.5 5v14"/></symbol>
    </svg>

    <div class="app-shell">
        <aside class="sidebar" id="sidebar" aria-label="Primary">
            <a href="/dashboard" class="sidebar-logo" aria-label="Saturn home">
                <img src="{{ asset('images/v5yuA.png') }}" alt="Saturn">
                <img src="{{ asset('images/text.svg') }}" alt="" class="logo-text">
            </a>

            <nav class="sidebar-nav" aria-label="Main">
                @foreach($nav as $s)
                    <div class="nav-section {{ $s['active'] && count($s['subs']) ? 'open' : '' }}" data-section="{{ $s['key'] }}">
                        <div class="nav-row">
                            <a href="{{ $s['href'] }}" class="nav-link {{ $s['active'] ? 'active' : '' }}" data-label="{{ $s['label'] }}" {!! $s['active'] ? 'aria-current="page"' : '' !!}>
                                <svg class="icon"><use href="#icon-{{ $s['icon'] }}"/></svg>
                                <span class="label">{{ $s['label'] }}</span>
                            </a>
                            @if(count($s['subs']))
                                <button type="button" class="nav-chev" aria-label="Toggle {{ $s['label'] }} menu" aria-expanded="{{ $s['active'] ? 'true' : 'false' }}">
                                    <svg class="icon"><use href="#icon-chevron-down"/></svg>
                                </button>
                            @endif
                        </div>
                        @if(count($s['subs']))
                            <div class="nav-subs">
                                @foreach($s['subs'] as $tab => $label)
                                    <a href="{{ $s['href'] }}#{{ $tab }}" class="nav-sublink" data-tab="{{ $tab }}">{{ $label }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </nav>

            <div class="sidebar-footer">
                <div class="user-chip" data-label="{{ Auth::user()->name }}">
                    <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <div class="meta">
                        <div class="name">{{ Auth::user()->name }}</div>
                        <div class="email">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-btn" data-label="Log out">
                        <svg class="icon"><use href="#icon-logout"/></svg><span class="label">Log out</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="main-col">
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="sb-toggle" id="sbToggle" aria-controls="sidebar" aria-expanded="true" aria-label="Collapse sidebar" title="Toggle sidebar ( [ )">
                        <svg class="icon"><use href="#icon-sidebar"/></svg>
                    </button>
                    <h1>@yield('title', 'Dashboard')</h1>
                </div>
                <div class="topbar-actions">
                    {{-- Hardcoded: search and notifications are visual only for now. They need a search endpoint and a notifications feed. --}}
                    <div class="topbar-search">
                        <svg class="icon"><use href="#icon-search"/></svg>
                        <input type="text" placeholder="Search assets…" aria-label="Search assets">
                    </div>
                    <button type="button" class="icon-btn-round" title="Notifications" aria-label="Notifications">
                        <svg class="icon"><use href="#icon-bell"/></svg>
                    </button>
                </div>
            </header>
            <main class="content" id="main">
                @yield('content')
            </main>
        </div>
    </div>

    <nav class="mobile-nav" aria-label="Mobile">
        @foreach($nav as $s)
            <a href="{{ $s['href'] }}" class="{{ $s['active'] ? 'active' : '' }}" {!! $s['active'] ? 'aria-current="page"' : '' !!}>
                <svg class="icon"><use href="#icon-{{ $s['icon'] }}"/></svg><span>{{ $s['short'] }}</span>
            </a>
        @endforeach
    </nav>

    <script>
    // ---------- Sidebar collapse ----------
    (function () {
        const root = document.documentElement;
        const btn = document.getElementById('sbToggle');
        function sync() {
            const collapsed = root.classList.contains('sb-collapsed');
            btn.setAttribute('aria-expanded', String(!collapsed));
            btn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        }
        function toggle() {
            root.classList.toggle('sb-collapsed');
            try { localStorage.setItem('saturn.sidebar', root.classList.contains('sb-collapsed') ? 'collapsed' : 'expanded'); } catch (e) {}
            sync();
        }
        btn.addEventListener('click', toggle);
        document.addEventListener('keydown', (e) => {
            if (e.key !== '[' || e.metaKey || e.ctrlKey || e.altKey) return;
            const t = e.target;
            if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.tagName === 'SELECT' || t.isContentEditable)) return;
            toggle();
        });
        sync();

        // Expand / collapse a section's sub-links
        document.querySelectorAll('.nav-chev').forEach((chev) => {
            chev.addEventListener('click', () => {
                const section = chev.closest('.nav-section');
                const open = section.classList.toggle('open');
                chev.setAttribute('aria-expanded', String(open));
            });
        });

        // Highlight the sub-link that matches the visible tab on the current page
        const here = location.pathname.replace(/\/$/, '');
        document.addEventListener('saturn:tab', (e) => {
            document.querySelectorAll('.nav-sublink').forEach((a) => {
                const path = a.getAttribute('href').split('#')[0].replace(/\/$/, '');
                a.classList.toggle('active', path === here && a.dataset.tab === e.detail.id);
            });
        });
    })();

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
                document.dispatchEvent(new CustomEvent('saturn:tab', { detail: { id: tabId } }));
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

        // Chart controller (legacy pages)
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