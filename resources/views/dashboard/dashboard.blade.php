@extends('layouts.app')
@section('title', 'Dashboard')

@push('styles')
<style>
    .dash { --line: rgba(255,255,255,0.07); }
    .dash .num { font-variant-numeric: tabular-nums; }
    .dash .eyebrow { font-size: .68rem; text-transform: uppercase; letter-spacing: .14em; color: var(--slate); font-weight: 600; }

    .d-hero { display: grid; grid-template-columns: 1.4fr 1fr; gap: 56px; padding-bottom: 40px; border-bottom: 1px solid var(--line); }
    .d-greet { font-size: .82rem; color: var(--slate); margin-bottom: 18px; }
    .d-total { font-size: clamp(2.4rem, 6vw, 3.8rem); font-weight: 300; letter-spacing: -.03em; line-height: 1.05; margin: 10px 0 14px; }
    .d-total .cents { font-size: .45em; color: var(--slate); font-weight: 400; }
    .d-delta { font-size: .92rem; font-weight: 600; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .d-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 28px 32px; align-content: center; }
    .d-metric .v { font-size: 1.2rem; font-weight: 600; margin-top: 6px; }
    .d-metric .sub { font-size: .76rem; color: var(--slate); margin-top: 2px; }

    .d-section { padding-top: 36px; }
    .d-head { display: flex; justify-content: space-between; align-items: baseline; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .d-head h2 { font-size: .95rem; font-weight: 700; }
    .d-head .lede { font-size: .8rem; color: var(--slate); margin-top: 3px; }
    .d-link { font-size: .8rem; color: var(--gold); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
    .d-link .icon { width: 14px; height: 14px; }

    .range { display: inline-flex; gap: 20px; }
    .range button { background: none; border: 0; color: var(--slate); font-weight: 700; font-size: .8rem; cursor: pointer; padding: 6px 0; border-bottom: 2px solid transparent; font-family: inherit; }
    .range button.active { color: var(--gold); border-bottom-color: var(--gold); }
    .chart-wrap { position: relative; }
    #dashChart { width: 100%; height: 260px; display: block; }
    .tip { position: absolute; top: 0; pointer-events: none; background: var(--midnight); border: 1px solid var(--gold-border); padding: 8px 10px; font-size: .76rem; border-radius: 6px; display: none; white-space: nowrap; transform: translateX(-50%); }
    .chart-foot { display: flex; justify-content: space-between; gap: 16px; font-size: .78rem; color: var(--slate); margin-top: 12px; flex-wrap: wrap; }

    .d-cols { display: grid; grid-template-columns: 1.4fr 1fr; gap: 56px; }
    .h-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 16px; align-items: center; padding: 16px 0; border-top: 1px solid var(--line); transition: background .15s; }
    a.h-row:hover { background: rgba(255,255,255,.02); }
    .h-list > :last-child { border-bottom: 1px solid var(--line); }
    .h-title { font-weight: 700; font-size: .9rem; }
    .h-sub { font-size: .76rem; color: var(--slate); margin-top: 2px; }
    .h-val { text-align: right; font-weight: 700; }

    .kv { display: flex; justify-content: space-between; align-items: baseline; padding: 14px 0; border-top: 1px solid var(--line); }
    .kv:last-of-type { border-bottom: 1px solid var(--line); }
    .kv .k { font-size: .84rem; color: var(--slate); }
    .kv .v { font-weight: 700; }
    .m-text { font-size: .86rem; color: var(--slate); line-height: 1.6; margin: 0 0 16px; }

    .empty { padding: 36px 0; color: var(--slate); font-size: .88rem; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); text-align: center; }
    .empty strong { display: block; color: var(--ivory); font-size: .95rem; margin-bottom: 6px; }
    .note { font-size: .76rem; color: var(--slate); margin-top: 14px; }

    @media (max-width: 980px) {
        .d-hero, .d-cols { grid-template-columns: 1fr; gap: 36px; }
    }
    @media (max-width: 680px) {
        #dashChart { height: 200px; }
        .d-metrics { gap: 22px 20px; }
    }
</style>
@endpush

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', Auth::user()->name)[0];
    $fmt = fn($n) => '€' . number_format($n, 2);

    [$whole, $cents] = explode('.', number_format($totalPortfolioValue, 2));

    $totalCost = (float) $holdings->sum('total_invested');
    $gainPct = $totalCost > 0 ? ($unrealizedGain / $totalCost) * 100 : null;

    // Top holdings by current value.
    $top = $holdings->map(function ($h) {
        $value = $h->shares_owned * ($h->asset->share_price ?? 0);
        $pct = $h->total_invested > 0 ? (($value - $h->total_invested) / $h->total_invested) * 100 : null;
        return ['h' => $h, 'value' => $value, 'pct' => $pct];
    })->sortByDesc('value')->take(4)->values();
    $holdingsTotal = (float) $holdings->sum(fn($h) => $h->shares_owned * ($h->asset->share_price ?? 0));

    // Real history only, same contract as the Portfolio page: [['date' => 'Y-m-d', 'value' => float], ...].
    // Hardcoded: controller must pass $performanceHistory. Until then the chart shows an empty state (no fabricated curve).
    $history = collect($performanceHistory ?? [])->map(fn($p) => ['d' => $p['date'], 'v' => (float) $p['value']])->values();
@endphp

<div class="dash">

    <div class="d-hero">
        <div>
            <div class="d-greet">{{ $greeting }}, {{ $firstName }}</div>
            <div class="eyebrow">Total portfolio value</div>
            <div class="d-total num">€{{ $whole }}<span class="cents">.{{ $cents }}</span></div>
            <div class="d-delta">
                <span class="num {{ $unrealizedGain >= 0 ? 'positive' : 'negative' }}">
                    {{ $unrealizedGain >= 0 ? '+' : '−' }}{{ $fmt(abs($unrealizedGain)) }}
                    @if($gainPct !== null)({{ $unrealizedGain >= 0 ? '+' : '−' }}{{ number_format(abs($gainPct), 2) }}%)@endif
                </span>
                <span class="muted" style="font-weight:500;">unrealised gain</span>
            </div>
        </div>
        <div class="d-metrics">
            <div class="d-metric"><div class="eyebrow">Invested</div><div class="v num">{{ $fmt($investedValue) }}</div></div>
            <div class="d-metric"><div class="eyebrow">Available cash</div><div class="v num">{{ $fmt($availableCash) }}</div></div>
            <div class="d-metric"><div class="eyebrow">Income received</div><div class="v num">{{ $fmt($incomeReceived) }}</div><div class="sub">{{ date('Y') }}</div></div>
            <div class="d-metric"><div class="eyebrow">Portfolio yield</div><div class="v num">{{ number_format($portfolioYield, 1) }}%</div></div>
        </div>
    </div>

    <div class="d-section">
        <div class="d-head">
            <div>
                <h2>Performance</h2>
                <div class="lede">Portfolio value over time.</div>
            </div>
            <div class="range" id="dashRange" @if($history->count() < 2) style="display:none;" @endif>
                <button type="button" data-range="1m">1M</button>
                <button type="button" data-range="6m">6M</button>
                <button type="button" class="active" data-range="1y">1Y</button>
                <button type="button" data-range="all">All</button>
            </div>
        </div>

        @if($history->count() >= 2)
            <div class="chart-wrap">
                <svg id="dashChart" preserveAspectRatio="none" role="img" aria-label="Portfolio value over time"></svg>
                <div class="tip" id="dashTip"></div>
            </div>
            <div class="chart-foot">
                <span id="dashPeriod"></span>
                <span id="dashChange" class="num"></span>
            </div>
            <div class="note">Value includes deposits and withdrawals made in the period. Past performance does not predict future results.</div>
        @else
            <div class="empty"><strong>Performance history isn't available yet</strong>We'll chart your portfolio here once historical valuations are recorded. No estimated figures are shown.</div>
        @endif
    </div>

    <div class="d-section d-cols">
        <div>
            <div class="d-head">
                <h2>Top holdings</h2>
                <a href="/portfolio#holdings" class="d-link">View all <svg class="icon"><use href="#icon-chevron-right"/></svg></a>
            </div>
            <div class="h-list">
                @forelse($top as $t)
                    @php $asset = $t['h']->asset; @endphp
                    <a class="h-row" href="/asset/{{ $asset->id ?? '' }}">
                        <div>
                            <div class="h-title">{{ $asset->title ?? 'Unknown asset' }}</div>
                            <div class="h-sub">
                                {{ $asset->category ?? 'Asset' }}
                                @if($holdingsTotal > 0) · <span class="num">{{ number_format(($t['value'] / $holdingsTotal) * 100, 1) }}%</span> of invested @endif
                                @if($t['pct'] !== null) · <span class="num {{ $t['pct'] >= 0 ? 'positive' : 'negative' }}">{{ $t['pct'] >= 0 ? '+' : '−' }}{{ number_format(abs($t['pct']), 1) }}%</span> @endif
                            </div>
                        </div>
                        <div class="h-val num">{{ $fmt($t['value']) }}</div>
                    </a>
                @empty
                    <div class="empty"><strong>No holdings yet</strong>Explore the market to make your first investment.
                        <div style="margin-top:12px;"><a class="d-link" href="{{ route('market') }}">Explore market →</a></div></div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="d-head">
                <h2>Income</h2>
                <a href="/income#cashflow" class="d-link">Details <svg class="icon"><use href="#icon-chevron-right"/></svg></a>
            </div>
            <div class="kv"><span class="k">This month</span><span class="v num">{{ $fmt($thisMonthIncome) }}</span></div>
            <div class="kv"><span class="k">{{ date('Y') }} total</span><span class="v num">{{ $fmt($incomeReceived) }}</span></div>

            <div class="d-head" style="margin-top:40px;">
                <h2>Market</h2>
            </div>
            <p class="m-text">
                {{ $activeOpportunitiesCount }} new {{ Str::plural('opportunity', $activeOpportunitiesCount) }} ·
                {{ $secondaryPositionsCount }} {{ Str::plural('position', $secondaryPositionsCount) }} on the secondary market.
            </p>
            <a href="{{ route('market') }}" class="btn btn-gold btn-block">Explore market</a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const history = @json($history);
    const svg = document.getElementById('dashChart');
    if (!svg || history.length < 2) return;

    const tip = document.getElementById('dashTip');
    const eur = n => '€' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const days = { '1m': 30, '6m': 182, '1y': 365, 'all': null };

    function slice(range) {
        if (!days[range]) return history;
        const cut = new Date(history[history.length - 1].d);
        cut.setDate(cut.getDate() - days[range]);
        return history.filter(p => new Date(p.d) >= cut);
    }

    function draw(range) {
        const s = slice(range);
        const period = document.getElementById('dashPeriod'), change = document.getElementById('dashChange');
        if (s.length < 2) {
            svg.innerHTML = ''; period.textContent = 'Not enough history for this period.'; change.textContent = '';
            return;
        }
        const first = s[0].v, last = s[s.length - 1].v, abs = last - first, ret = first > 0 ? abs / first * 100 : 0;
        period.textContent = s[0].d + ' → ' + s[s.length - 1].d;
        change.textContent = (abs >= 0 ? '+' : '−') + eur(Math.abs(abs)) + ' (' + (abs >= 0 ? '+' : '−') + Math.abs(ret).toFixed(2) + '%)';
        change.style.color = abs >= 0 ? 'var(--green)' : 'var(--red)';

        const W = 800, H = 260, pad = 8;
        const min = Math.min(...s.map(p => p.v)), max = Math.max(...s.map(p => p.v)), span = (max - min) || 1;
        const x = i => (i / (s.length - 1)) * W, y = v => H - pad - ((v - min) / span) * (H - pad * 2);
        svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
        svg.innerHTML =
            '<line x1="0" x2="' + W + '" y1="' + (H - 1) + '" y2="' + (H - 1) + '" stroke="rgba(255,255,255,.08)"/>' +
            '<polyline fill="none" stroke="#C6A15B" stroke-width="2" vector-effect="non-scaling-stroke" points="' + s.map((p, i) => x(i).toFixed(1) + ',' + y(p.v).toFixed(1)).join(' ') + '"/>' +
            '<line id="hl" y1="0" y2="' + H + '" stroke="rgba(245,242,234,.25)" vector-effect="non-scaling-stroke" style="display:none"/>';

        svg.onmousemove = svg.ontouchmove = e => {
            const pt = e.touches ? e.touches[0] : e, box = svg.getBoundingClientRect();
            const i = Math.max(0, Math.min(s.length - 1, Math.round(((pt.clientX - box.left) / box.width) * (s.length - 1))));
            const hl = svg.querySelector('#hl'); hl.setAttribute('x1', x(i)); hl.setAttribute('x2', x(i)); hl.style.display = '';
            tip.style.display = 'block'; tip.style.left = (x(i) / W * box.width) + 'px';
            tip.innerHTML = '<div style="color:var(--slate)">' + s[i].d + '</div><div class="num" style="font-weight:700;">' + eur(s[i].v) + '</div>';
        };
        svg.onmouseleave = () => { tip.style.display = 'none'; const hl = svg.querySelector('#hl'); if (hl) hl.style.display = 'none'; };
    }

    document.querySelectorAll('#dashRange button').forEach(btn => btn.addEventListener('click', () => {
        document.querySelectorAll('#dashRange button').forEach(b => b.classList.toggle('active', b === btn));
        draw(btn.dataset.range);
    }));
    draw('1y');
})();
</script>
@endpush