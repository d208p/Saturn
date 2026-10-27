@extends('layouts.app')
@section('title', 'Portfolio')

@php
    $holdings = $user->holdings ?? collect();
    $availableCash = (float) ($user->balance ?? 0);

    // Normalised rows used by the Holdings and Allocation tabs.
    $rows = $holdings->map(function ($h) {
        $a = $h->asset;
        $price = (float) ($a->share_price ?? 0);
        $value = $h->shares_owned * $price;
        // Cost basis: only shown when the data exists. Hardcoded field names — adjust to the real schema.
        $avg = $h->average_price ?? $h->purchase_price ?? null;
        $cost = $avg !== null ? $h->shares_owned * (float) $avg : null;
        return [
            'id'       => $a->id ?? null,
            'title'    => $a->title ?? 'Unknown asset',
            'category' => $a->category ?? 'Other',
            'country'  => $a->country ?? 'Unspecified', // Hardcoded fallback: asset country field name may differ
            'shares'   => (float) $h->shares_owned,
            'value'    => $value,
            'cost'     => $cost,
            'gain'     => $cost !== null ? $value - $cost : null,
            'gainPct'  => ($cost !== null && $cost > 0) ? (($value - $cost) / $cost) * 100 : null,
        ];
    })->values();

    $holdingsValue = (float) $rows->sum('value');
    $portfolioValue = $holdingsValue + $availableCash;
    $rows = $rows->map(function ($r) use ($holdingsValue) {
        $r['weight'] = $holdingsValue > 0 ? ($r['value'] / $holdingsValue) * 100 : 0;
        return $r;
    });

    $categories = $rows->pluck('category')->unique()->sort()->values();
    $countries  = $rows->pluck('country')->unique()->sort()->values();

    $byCategory = $rows->groupBy('category')->map(fn($g) => $g->sum('value'))->sortDesc();

    // Total unrealised gain, only if every position has a cost basis.
    $allHaveCost = $rows->count() > 0 && $rows->every(fn($r) => $r['cost'] !== null);
    $totalGain = $allHaveCost ? $rows->sum('gain') : null;
    $totalCost = $allHaveCost ? $rows->sum('cost') : null;
    $totalGainPct = ($allHaveCost && $totalCost > 0) ? ($totalGain / $totalCost) * 100 : null;

    // Real history must be provided by the controller as [['date' => 'Y-m-d', 'value' => float], ...].
    // Hardcoded: no historical snapshots source exists in this view, so the chart shows an empty state until supplied.
    $history = collect($performanceHistory ?? [])->map(fn($p) => ['d' => $p['date'], 'v' => (float) $p['value']])->values();
    // Optional benchmark in the same shape (e.g. $benchmarkHistory). Hardcoded: omitted when absent.
    $benchmark = collect($benchmarkHistory ?? [])->map(fn($p) => ['d' => $p['date'], 'v' => (float) $p['value']])->values();

    $fmt = fn($n) => '€' . number_format($n, 2);
@endphp

@push('styles')
<style>
    .pf { --line: rgba(255,255,255,0.07); }
    .pf .tabs { border-bottom: 1px solid var(--line); gap: 28px; margin-bottom: 40px; }
    .pf .tab-btn { padding: 12px 0; font-size: .88rem; letter-spacing: .01em; }
    .pf .eyebrow { font-size: .68rem; text-transform: uppercase; letter-spacing: .14em; color: var(--slate); font-weight: 600; }
    .pf .num { font-variant-numeric: tabular-nums; }

    /* Hero */
    .pf-hero { display: grid; grid-template-columns: 1.4fr 1fr; gap: 56px; padding-bottom: 40px; border-bottom: 1px solid var(--line); }
    .pf-total { font-size: clamp(2.4rem, 6vw, 3.8rem); font-weight: 300; letter-spacing: -.03em; line-height: 1.05; margin: 10px 0 14px; }
    .pf-total .cents { font-size: .45em; color: var(--slate); font-weight: 400; }
    .pf-delta { font-size: .92rem; font-weight: 600; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .pf-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 28px 32px; align-content: center; }
    .pf-metric .v { font-size: 1.25rem; font-weight: 600; margin-top: 6px; }
    .pf-metric .sub { font-size: .76rem; color: var(--slate); margin-top: 2px; }

    .pf-section { padding-top: 36px; }
    .pf-section h2 { font-size: .95rem; font-weight: 700; margin-bottom: 4px; }
    .pf-section .lede { font-size: .82rem; color: var(--slate); margin-bottom: 20px; max-width: 60ch; }

    /* Risk */
    .risk-head { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .risk-meter { display: flex; gap: 4px; margin: 10px 0 24px; max-width: 360px; }
    .risk-meter i { flex: 1; height: 4px; border-radius: 2px; background: var(--line); }
    .risk-meter i.on { background: var(--gold); }
    .assess { display: grid; grid-template-columns: 200px 140px 1fr; gap: 20px; align-items: baseline; padding: 16px 0; border-top: 1px solid var(--line); }
    .assess .name { font-weight: 600; font-size: .88rem; }
    .assess .val { font-weight: 700; font-size: .88rem; }
    .assess .why { font-size: .82rem; color: var(--slate); }

    /* Holdings toolbar */
    .toolbar { display: grid; grid-template-columns: 1.4fr repeat(4, 1fr) auto; gap: 10px; margin-bottom: 8px; align-items: end; }
    .field label { display: block; font-size: .64rem; text-transform: uppercase; letter-spacing: .12em; color: var(--slate); margin-bottom: 6px; font-weight: 600; }
    .field input, .field select {
        width: 100%; background: transparent; border: 0; border-bottom: 1px solid var(--line);
        color: var(--ivory); font-family: inherit; font-size: .85rem; padding: 8px 2px; outline: none; border-radius: 0;
    }
    .field select option { background: var(--midnight); color: var(--ivory); }
    .field input:focus, .field select:focus { border-bottom-color: var(--gold); }
    .link-btn { background: none; border: 0; color: var(--gold); font-size: .8rem; font-weight: 600; cursor: pointer; padding: 8px 4px; white-space: nowrap; }
    .link-btn:disabled { color: var(--slate); opacity: .5; cursor: default; }
    .result-count { font-size: .78rem; color: var(--slate); margin: 14px 0 6px; }

    .hold-table { width: 100%; border-collapse: collapse; }
    .hold-table th { font-size: .64rem; padding: 12px 10px; border-bottom: 1px solid var(--line); }
    .hold-table th.r, .hold-table td.r { text-align: right; }
    .hold-table td { padding: 16px 10px; border-bottom: 1px solid var(--line); vertical-align: middle; }
    .hold-table tbody tr { cursor: pointer; transition: background .15s; }
    .hold-table tbody tr:hover { background: rgba(255,255,255,.025); }
    .hold-table tbody tr:focus-visible { outline: 1px solid var(--gold-border); outline-offset: -1px; }
    .asset-name { font-weight: 700; font-size: .9rem; }
    .asset-meta { font-size: .76rem; color: var(--slate); margin-top: 2px; }
    .weight { display: flex; align-items: center; gap: 10px; justify-content: flex-end; }
    .weight .bar { width: 56px; height: 3px; background: var(--line); border-radius: 2px; overflow: hidden; }
    .weight .bar b { display: block; height: 100%; background: var(--slate); }

    .empty { text-align: center; padding: 56px 16px; color: var(--slate); font-size: .88rem; }
    .empty strong { display: block; color: var(--ivory); font-size: .95rem; margin-bottom: 6px; }
    .empty .link-btn { margin-top: 10px; }

    /* Allocation */
    .seg { display: inline-flex; border-bottom: 1px solid var(--line); gap: 24px; margin-bottom: 28px; }
    .seg button { background: none; border: 0; color: var(--slate); font-size: .85rem; font-weight: 600; padding: 8px 0; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -1px; }
    .seg button.active { color: var(--gold); border-bottom-color: var(--gold); }
    .alloc-view { display: none; }
    .alloc-view.active { display: block; }
    .alloc { display: grid; grid-template-columns: 180px 1fr 70px 120px; gap: 18px; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--line); font-size: .88rem; }
    .alloc .bar { height: 6px; background: var(--line); border-radius: 3px; overflow: hidden; }
    .alloc .bar b { display: block; height: 100%; background: var(--gold); border-radius: 3px; }
    .alloc .pct { text-align: right; font-weight: 700; }
    .alloc .amt { text-align: right; color: var(--slate); }

    /* Performance */
    .perf-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; flex-wrap: wrap; margin-bottom: 24px; }
    .range { display: inline-flex; gap: 20px; }
    .range button { background: none; border: 0; color: var(--slate); font-weight: 700; font-size: .8rem; cursor: pointer; padding: 6px 0; border-bottom: 2px solid transparent; }
    .range button.active { color: var(--gold); border-bottom-color: var(--gold); }
    .perf-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; margin-bottom: 28px; }
    .perf-stats .v { font-size: 1.35rem; font-weight: 600; margin-top: 6px; }
    .chart-wrap { position: relative; }
    #perfChart { width: 100%; height: 300px; display: block; }
    .tip { position: absolute; top: 0; pointer-events: none; background: var(--midnight); border: 1px solid var(--gold-border); padding: 8px 10px; font-size: .76rem; border-radius: 6px; display: none; white-space: nowrap; transform: translateX(-50%); }
    .legend { display: flex; gap: 18px; font-size: .76rem; color: var(--slate); margin-top: 12px; }
    .legend i { display: inline-block; width: 14px; height: 2px; vertical-align: middle; margin-right: 6px; }
    .note { font-size: .76rem; color: var(--slate); margin-top: 20px; max-width: 70ch; }

    .skeleton { background: linear-gradient(90deg, var(--line), rgba(255,255,255,.12), var(--line)); background-size: 200% 100%; animation: sk 1.3s infinite; border-radius: 4px; }
    @keyframes sk { to { background-position: -200% 0; } }

    @media (max-width: 980px) {
        .pf-hero { grid-template-columns: 1fr; gap: 32px; }
        .toolbar { grid-template-columns: 1fr 1fr; }
        .toolbar .field.search { grid-column: 1 / -1; }
        .assess { grid-template-columns: 1fr auto; gap: 4px 12px; }
        .assess .why { grid-column: 1 / -1; }
        .perf-stats { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 680px) {
        .pf .tabs { gap: 20px; }
        .hold-table thead { display: none; }
        .hold-table, .hold-table tbody, .hold-table tr, .hold-table td { display: block; width: 100%; }
        .hold-table tr { padding: 16px 0; border-bottom: 1px solid var(--line); display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; }
        .hold-table td { border: 0; padding: 0; text-align: left !important; }
        .hold-table td.c-asset { grid-column: 1; }
        .hold-table td.c-value { grid-column: 2; grid-row: 1; text-align: right !important; font-weight: 700; }
        .hold-table td.c-cat, .hold-table td.c-qty, .hold-table td.c-weight, .hold-table td.c-perf { font-size: .78rem; color: var(--slate); grid-column: 1 / -1; }
        .hold-table td[data-label]::before { content: attr(data-label) "  "; font-size: .64rem; text-transform: uppercase; letter-spacing: .1em; margin-right: 6px; }
        .weight { justify-content: flex-start; }
        .alloc { grid-template-columns: 1fr auto; gap: 8px 12px; }
        .alloc .bar { grid-column: 1 / -1; grid-row: 2; }
        .alloc .amt { grid-column: 1 / -1; text-align: left; }
        #perfChart { height: 220px; }
    }
</style>
@endpush

@section('content')
<div class="pf">

    <div class="tabs" data-panels="#portfolio-panels">
        <button class="tab-btn active" data-tab="overview">Overview</button>
        <button class="tab-btn" data-tab="holdings">Holdings</button>
        <button class="tab-btn" data-tab="allocation">Allocation</button>
        <button class="tab-btn" data-tab="performance">Performance</button>
    </div>

    <div id="portfolio-panels">

        {{-- ================= OVERVIEW ================= --}}
        <div class="tab-panel active" id="overview">
            <div class="pf-hero">
                <div>
                    <div class="eyebrow">Total portfolio value</div>
                    @php [$whole, $cents] = explode('.', number_format($portfolioValue, 2)); @endphp
                    <div class="pf-total num">€{{ $whole }}<span class="cents">.{{ $cents }}</span></div>
                    <div class="pf-delta">
                        @if($totalGain !== null)
                            <span class="num {{ $totalGain >= 0 ? 'positive' : 'negative' }}">
                                {{ $totalGain >= 0 ? '+' : '−' }}{{ $fmt(abs($totalGain)) }}
                                @if($totalGainPct !== null) ({{ $totalGain >= 0 ? '+' : '−' }}{{ number_format(abs($totalGainPct), 2) }}%) @endif
                            </span>
                            <span class="muted" style="font-weight:500;">unrealised, versus purchase cost</span>
                        @else
                            <span class="muted" style="font-weight:500;">Gain and loss will appear once purchase cost is available for your positions.</span>
                        @endif
                    </div>
                </div>
                <div class="pf-metrics">
                    <div class="pf-metric">
                        <div class="eyebrow">Invested</div>
                        <div class="v num">{{ $fmt($holdingsValue) }}</div>
                        <div class="sub">{{ $portfolioValue > 0 ? number_format(($holdingsValue / $portfolioValue) * 100, 1) : '0.0' }}% of portfolio</div>
                    </div>
                    <div class="pf-metric">
                        <div class="eyebrow">Available cash</div>
                        <div class="v num">{{ $fmt($availableCash) }}</div>
                        <div class="sub">{{ $portfolioValue > 0 ? number_format(($availableCash / $portfolioValue) * 100, 1) : '0.0' }}% of portfolio</div>
                    </div>
                    <div class="pf-metric">
                        <div class="eyebrow">Positions</div>
                        <div class="v num">{{ $rows->count() }}</div>
                        <div class="sub">{{ $categories->count() }} {{ Str::plural('asset class', $categories->count()) }}</div>
                    </div>
                    <div class="pf-metric">
                        <div class="eyebrow">Countries</div>
                        <div class="v num">{{ $countries->reject(fn($c) => $c === 'Unspecified')->count() }}</div>
                        <div class="sub">geographic exposure</div>
                    </div>
                </div>
            </div>

            <div class="pf-section">
                <div class="risk-head">
                    <div>
                        <h2>Portfolio risk</h2>
                        <div class="lede">How your current positions behave together. A description of today's mix, not a recommendation.</div>
                    </div>
                    <span class="badge {{ $riskBadgeClass }}">{{ $riskLabel }} · {{ $riskScore }} / 10</span>
                </div>
                <div class="risk-meter" aria-hidden="true">
                    @for($i = 1; $i <= 10; $i++)<i class="{{ $i <= (int) round($riskScore) ? 'on' : '' }}"></i>@endfor
                </div>

                {{-- Explanations are static copy describing each measure. --}}
                <div class="assess">
                    <div class="name">Diversification</div>
                    <div class="val {{ $diversification['class'] }}">{{ $diversification['label'] }}</div>
                    <div class="why">How widely your capital is spread across assets and asset classes.</div>
                </div>
                <div class="assess">
                    <div class="name">Liquidity</div>
                    <div class="val {{ $liquidity['class'] }}">{{ $liquidity['label'] }}</div>
                    <div class="why">How easily positions can be sold on the secondary market without waiting for a buyer.</div>
                </div>
                <div class="assess">
                    <div class="name">Asset concentration</div>
                    <div class="val {{ $concentration['class'] }}">{{ $concentration['label'] }}</div>
                    <div class="why">How much of the portfolio rests on a small number of positions.</div>
                </div>
                <div class="assess">
                    <div class="name">Geographic exposure</div>
                    <div class="val {{ $geographicRisk['class'] }}">{{ $geographicRisk['label'] }}</div>
                    <div class="why">How dependent your holdings are on the economy and regulation of a single country.</div>
                </div>
                <div class="assess" style="border-bottom:1px solid var(--line);">
                    <div class="name">Development exposure</div>
                    <div class="val {{ $devExposure['class'] }}">{{ $devExposure['label'] }}</div>
                    <div class="why">The share of value tied to projects still under development, which carry completion risk.</div>
                </div>
                <div class="note">This score does not guarantee any outcome. Past composition is not an indicator of future results.</div>
            </div>
        </div>

        {{-- ================= HOLDINGS ================= --}}
        <div class="tab-panel" id="holdings">
            <div class="toolbar" role="search">
                <div class="field search"><label for="f-search">Search</label><input id="f-search" type="search" placeholder="Asset name" autocomplete="off"></div>
                <div class="field"><label for="f-cat">Category</label>
                    <select id="f-cat"><option value="">All</option>@foreach($categories as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></div>
                <div class="field"><label for="f-country">Country</label>
                    <select id="f-country"><option value="">All</option>@foreach($countries as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach</select></div>
                <div class="field"><label for="f-perf">Performance</label>
                    <select id="f-perf"><option value="">All</option><option value="gain">In gain</option><option value="loss">In loss</option><option value="na">Not available</option></select></div>
                <div class="field"><label for="f-size">Position size</label>
                    <select id="f-size"><option value="">All</option><option value="large">Large (10%+)</option><option value="medium">Medium (2–10%)</option><option value="small">Small (under 2%)</option></select></div>
                <div class="field"><label for="f-sort">Sort by</label>
                    <select id="f-sort">
                        <option value="value-desc">Value, high to low</option>
                        <option value="value-asc">Value, low to high</option>
                        <option value="weight-desc">Weight</option>
                        <option value="perf-desc">Performance, best first</option>
                        <option value="perf-asc">Performance, worst first</option>
                        <option value="name-asc">Name, A–Z</option>
                    </select></div>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div class="result-count" id="holdCount" aria-live="polite"></div>
                <button type="button" class="link-btn" id="resetFilters" disabled>Reset filters</button>
            </div>

            @if($rows->isEmpty())
                <div class="empty"><strong>No positions yet</strong>Your holdings will appear here after your first purchase.
                    <div><a class="link-btn" href="/market">Explore the market →</a></div></div>
            @else
            <table class="hold-table" id="holdTable">
                <thead>
                    <tr><th>Asset</th><th>Category</th><th class="r">Shares</th><th class="r">Value</th><th class="r">Weight</th><th class="r">Return</th></tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        <tr tabindex="0" role="link" data-href="/asset/{{ $r['id'] }}"
                            data-name="{{ Str::lower($r['title']) }}" data-cat="{{ $r['category'] }}" data-country="{{ $r['country'] }}"
                            data-value="{{ round($r['value'], 2) }}" data-weight="{{ round($r['weight'], 4) }}"
                            data-perf="{{ $r['gainPct'] !== null ? round($r['gainPct'], 4) : '' }}">
                            <td class="c-asset"><div class="asset-name">{{ $r['title'] }}</div><div class="asset-meta">{{ $r['country'] }}</div></td>
                            <td class="c-cat muted" data-label="Category">{{ $r['category'] }}</td>
                            <td class="c-qty r num" data-label="Shares">{{ number_format($r['shares']) }}</td>
                            <td class="c-value r num" style="font-weight:700;">{{ $fmt($r['value']) }}</td>
                            <td class="c-weight r num" data-label="Weight">
                                <div class="weight"><span>{{ number_format($r['weight'], 1) }}%</span><span class="bar"><b style="width: {{ min($r['weight'], 100) }}%"></b></span></div>
                            </td>
                            <td class="c-perf r num" data-label="Return">
                                @if($r['gainPct'] !== null)
                                    <span class="{{ $r['gainPct'] >= 0 ? 'positive' : 'negative' }}">{{ $r['gainPct'] >= 0 ? '+' : '−' }}{{ number_format(abs($r['gainPct']), 2) }}%</span>
                                @else
                                    <span class="muted" title="Purchase price unavailable">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="empty" id="holdEmpty" style="display:none;"><strong>No positions match these filters</strong>Try widening your search.
                <div><button type="button" class="link-btn" id="resetFilters2">Reset filters</button></div></div>
            @endif
        </div>

        {{-- ================= ALLOCATION ================= --}}
        <div class="tab-panel" id="allocation">
            <div class="seg" role="tablist">
                <button type="button" class="active" data-alloc="class">Asset class</button>
                <button type="button" data-alloc="geo">Geography</button>
            </div>

            <div class="alloc-view active" id="alloc-class">
                @forelse($byCategory as $cat => $val)
                    @php $p = $holdingsValue > 0 ? ($val / $holdingsValue) * 100 : 0; @endphp
                    <div class="alloc">
                        <span style="font-weight:600;">{{ $cat }}</span>
                        <span class="bar"><b style="width: {{ min($p, 100) }}%"></b></span>
                        <span class="pct num">{{ number_format($p, 1) }}%</span>
                        <span class="amt num">{{ $fmt($val) }}</span>
                    </div>
                @empty
                    <div class="empty"><strong>Nothing to allocate yet</strong>Allocation appears once you hold a position.</div>
                @endforelse
            </div>

            <div class="alloc-view" id="alloc-geo">
                @forelse($geographicAllocation as $g)
                    <div class="alloc">
                        <span style="font-weight:600;">{{ $g['country'] }}</span>
                        <span class="bar"><b style="width: {{ min($g['percentage'], 100) }}%"></b></span>
                        <span class="pct num">{{ $g['percentage'] }}%</span>
                        <span class="amt num">{{ $fmt($holdingsValue * $g['percentage'] / 100) }}</span>
                    </div>
                @empty
                    <div class="empty"><strong>Nothing to allocate yet</strong>Geographic allocation appears once you hold a position.</div>
                @endforelse
            </div>
            <div class="note">Allocation is calculated on invested holdings at current share prices and excludes available cash.</div>
        </div>

        {{-- ================= PERFORMANCE ================= --}}
        <div class="tab-panel" id="performance">
            <div class="perf-head">
                <div>
                    <div class="eyebrow">Portfolio value over time</div>
                </div>
                <div class="range" id="perfRange">
                    <button type="button" data-range="1m">1M</button>
                    <button type="button" data-range="6m">6M</button>
                    <button type="button" class="active" data-range="1y">1Y</button>
                    <button type="button" data-range="all">All</button>
                </div>
            </div>

            <div id="perfLoading" style="display:none;"><div class="skeleton" style="height:300px;"></div></div>

            <div id="perfContent">
                <div class="perf-stats">
                    <div><div class="eyebrow">Return</div><div class="v num" id="ps-ret">—</div></div>
                    <div><div class="eyebrow">Gain / loss</div><div class="v num" id="ps-abs">—</div></div>
                    <div><div class="eyebrow">Start value</div><div class="v num" id="ps-start">—</div></div>
                    <div><div class="eyebrow">Benchmark</div><div class="v num" id="ps-bench">—</div></div>
                </div>
                <div class="chart-wrap">
                    <svg id="perfChart" preserveAspectRatio="none" role="img" aria-label="Portfolio value over time"></svg>
                    <div class="tip" id="perfTip"></div>
                </div>
                <div class="legend" id="perfLegend"></div>
            </div>

            <div class="empty" id="perfEmpty" style="display:none;"><strong>Performance history isn't available yet</strong>
                We'll chart your portfolio here as soon as historical valuations are recorded. No estimated figures are shown.</div>

            <div class="note">Portfolio value includes deposits and withdrawals made in the period, so the change shown is not purely investment return. Past performance does not predict future results.</div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const eur = (n) => '€' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const signed = (n, f) => (n >= 0 ? '+' : '−') + f(Math.abs(n));
    const cls = (n) => n >= 0 ? 'positive' : 'negative';

    /* ---------- Holdings: filter, sort, reset ---------- */
    const table = document.getElementById('holdTable');
    if (table) {
        const tbody = table.tBodies[0];
        const rows = Array.from(tbody.rows);
        const el = (id) => document.getElementById(id);
        const f = { search: el('f-search'), cat: el('f-cat'), country: el('f-country'), perf: el('f-perf'), size: el('f-size'), sort: el('f-sort') };
        const defaults = { search: '', cat: '', country: '', perf: '', size: '', sort: 'value-desc' };

        function apply() {
            const q = f.search.value.trim().toLowerCase();
            let visible = 0;
            rows.forEach(r => {
                const d = r.dataset, w = parseFloat(d.weight), p = d.perf === '' ? null : parseFloat(d.perf);
                let ok = (!q || d.name.includes(q)) && (!f.cat.value || d.cat === f.cat.value) && (!f.country.value || d.country === f.country.value);
                if (ok && f.perf.value) ok = f.perf.value === 'na' ? p === null : p !== null && (f.perf.value === 'gain' ? p >= 0 : p < 0);
                if (ok && f.size.value) ok = f.size.value === 'large' ? w >= 10 : f.size.value === 'medium' ? (w >= 2 && w < 10) : w < 2;
                r.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            const [key, dir] = f.sort.value.split('-'), m = dir === 'asc' ? 1 : -1;
            rows.sort((a, b) => {
                if (key === 'name') return a.dataset.name.localeCompare(b.dataset.name) * m;
                const get = (r) => key === 'perf' ? (r.dataset.perf === '' ? null : parseFloat(r.dataset.perf)) : parseFloat(r.dataset[key]);
                const x = get(a), y = get(b);
                if (x === null && y === null) return 0;
                if (x === null) return 1;   // missing data always last
                if (y === null) return -1;
                return (x - y) * m;
            }).forEach(r => tbody.appendChild(r));

            el('holdCount').textContent = visible + ' of ' + rows.length + ' ' + (rows.length === 1 ? 'position' : 'positions');
            el('holdEmpty').style.display = visible ? 'none' : 'block';
            table.style.display = visible ? '' : 'none';
            el('resetFilters').disabled = Object.keys(f).every(k => f[k].value === defaults[k]);
        }
        function reset() { Object.keys(f).forEach(k => f[k].value = defaults[k]); apply(); }

        Object.values(f).forEach(i => i.addEventListener(i.tagName === 'INPUT' ? 'input' : 'change', apply));
        el('resetFilters').addEventListener('click', reset);
        el('resetFilters2').addEventListener('click', reset);
        rows.forEach(r => {
            const go = () => { if (r.dataset.href && !r.dataset.href.endsWith('/')) window.location.href = r.dataset.href; };
            r.addEventListener('click', go);
            r.addEventListener('keydown', e => { if (e.key === 'Enter') go(); });
        });
        apply();
    }

    /* ---------- Allocation view switch ---------- */
    document.querySelectorAll('[data-alloc]').forEach(b => b.addEventListener('click', () => {
        document.querySelectorAll('[data-alloc]').forEach(x => x.classList.toggle('active', x === b));
        document.getElementById('alloc-class').classList.toggle('active', b.dataset.alloc === 'class');
        document.getElementById('alloc-geo').classList.toggle('active', b.dataset.alloc === 'geo');
    }));

    /* ---------- Performance (real data only) ---------- */
    const history = @json($history);
    const benchmark = @json($benchmark);
    const svg = document.getElementById('perfChart'), tip = document.getElementById('perfTip');

    if (history.length < 2) {
        document.getElementById('perfContent').style.display = 'none';
        document.getElementById('perfRange').style.display = 'none';
        document.getElementById('perfEmpty').style.display = 'block';
        return;
    }

    const days = { '1m': 30, '6m': 182, '1y': 365, 'all': null };
    const slice = (series, range) => {
        if (!days[range]) return series;
        const last = new Date(series[series.length - 1].d), cut = new Date(last);
        cut.setDate(cut.getDate() - days[range]);
        return series.filter(p => new Date(p.d) >= cut);
    };

    function draw(range) {
        const s = slice(history, range);
        if (s.length < 2) {
            svg.innerHTML = '';
            ['ps-ret','ps-abs','ps-start','ps-bench'].forEach(i => document.getElementById(i).textContent = '—');
            document.getElementById('perfLegend').textContent = 'Not enough history for this period.';
            return;
        }
        const first = s[0].v, last = s[s.length - 1].v, abs = last - first, ret = first > 0 ? abs / first * 100 : 0;
        const r = document.getElementById('ps-ret'), a = document.getElementById('ps-abs');
        r.textContent = signed(ret, n => n.toFixed(2) + '%'); r.className = 'v num ' + cls(ret);
        a.textContent = signed(abs, eur); a.className = 'v num ' + cls(abs);
        document.getElementById('ps-start').textContent = eur(first);

        const b = benchmark.length ? slice(benchmark, range) : [];
        const bEl = document.getElementById('ps-bench');
        if (b.length >= 2 && b[0].v > 0) {
            const br = (b[b.length - 1].v - b[0].v) / b[0].v * 100;
            bEl.textContent = signed(br, n => n.toFixed(2) + '%'); bEl.className = 'v num ' + cls(br);
        } else { bEl.textContent = 'Not available'; bEl.className = 'v muted'; }

        const W = 800, H = 300, pad = 8;
        const min = Math.min(...s.map(p => p.v)), max = Math.max(...s.map(p => p.v)), span = (max - min) || 1;
        const x = i => (i / (s.length - 1)) * W;
        const y = v => H - pad - ((v - min) / span) * (H - pad * 2);
        const pts = s.map((p, i) => x(i).toFixed(1) + ',' + y(p.v).toFixed(1));
        svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
        svg.innerHTML =
            '<line x1="0" x2="' + W + '" y1="' + (H - 1) + '" y2="' + (H - 1) + '" stroke="rgba(255,255,255,.08)"/>' +
            '<polyline fill="none" stroke="#C6A15B" stroke-width="2" vector-effect="non-scaling-stroke" points="' + pts.join(' ') + '"/>' +
            '<line id="hl" y1="0" y2="' + H + '" stroke="rgba(245,242,234,.25)" vector-effect="non-scaling-stroke" style="display:none"/>';
        document.getElementById('perfLegend').innerHTML = '<span><i style="background:#C6A15B"></i>Portfolio value</span><span>' + s[0].d + ' → ' + s[s.length - 1].d + '</span>';

        svg.onmousemove = svg.ontouchmove = (e) => {
            const pt = e.touches ? e.touches[0] : e, box = svg.getBoundingClientRect();
            const i = Math.max(0, Math.min(s.length - 1, Math.round(((pt.clientX - box.left) / box.width) * (s.length - 1))));
            const hl = svg.querySelector('#hl'); hl.setAttribute('x1', x(i)); hl.setAttribute('x2', x(i)); hl.style.display = '';
            tip.style.display = 'block'; tip.style.left = (x(i) / W * box.width) + 'px';
            tip.innerHTML = '<div class="muted">' + s[i].d + '</div><div class="num" style="font-weight:700;">' + eur(s[i].v) + '</div>';
        };
        svg.onmouseleave = () => { tip.style.display = 'none'; const hl = svg.querySelector('#hl'); if (hl) hl.style.display = 'none'; };
    }

    document.querySelectorAll('#perfRange button').forEach(btn => btn.addEventListener('click', () => {
        document.querySelectorAll('#perfRange button').forEach(x => x.classList.toggle('active', x === btn));
        draw(btn.dataset.range);
    }));
    draw('1y');
})();
</script>
@endpush