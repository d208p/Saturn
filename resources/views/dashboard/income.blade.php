@extends('layouts.app')

@section('title', 'Income')

@push('styles')
<style>
    .inc { --line: rgba(255,255,255,0.07); }
    .inc .tabs { border-bottom: 1px solid var(--line); gap: 28px; margin-bottom: 40px; }
    .inc .tab-btn { padding: 12px 0; font-size: .88rem; letter-spacing: .01em; }
    .inc .num { font-variant-numeric: tabular-nums; }
    .inc .eyebrow { font-size: .68rem; text-transform: uppercase; letter-spacing: .14em; color: var(--slate); font-weight: 600; }

    .inc-hero { display: grid; grid-template-columns: 1.4fr 1fr; gap: 56px; padding-bottom: 36px; border-bottom: 1px solid var(--line); }
    .inc-total { font-size: clamp(2.2rem, 5.5vw, 3.4rem); font-weight: 300; letter-spacing: -.03em; line-height: 1.05; margin: 10px 0 10px; }
    .inc-total .cents { font-size: .45em; color: var(--slate); font-weight: 400; }
    .inc-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 28px 32px; align-content: center; }
    .inc-metric .v { font-size: 1.2rem; font-weight: 600; margin-top: 6px; }
    .inc-metric .sub { font-size: .76rem; color: var(--slate); margin-top: 2px; }

    .inc-section { padding-top: 36px; }
    .inc-section h2 { font-size: .95rem; font-weight: 700; margin-bottom: 4px; }
    .inc-section .lede { font-size: .82rem; color: var(--slate); margin-bottom: 24px; max-width: 60ch; }
    .section-head { display: flex; justify-content: space-between; align-items: baseline; gap: 16px; flex-wrap: wrap; }

    /* Monthly chart */
    .mchart { position: relative; display: flex; align-items: stretch; gap: 10px; height: 220px; padding-top: 8px; border-bottom: 1px solid var(--line); }
    .mcol { flex: 1; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; height: 100%; cursor: default; outline: none; position: relative; }
    .mcol .mbar { width: 100%; max-width: 32px; background: rgba(198,161,91,.45); border-radius: 3px 3px 0 0; min-height: 2px; transition: background .15s; }
    .mcol:hover .mbar, .mcol:focus-visible .mbar, .mcol.top .mbar { background: var(--gold); }
    .mcol.zero .mbar { background: var(--line); }
    .mcol .mtip { position: absolute; bottom: calc(100% + 6px); left: 50%; transform: translateX(-50%); background: var(--midnight); border: 1px solid var(--gold-border); border-radius: 6px; padding: 6px 9px; font-size: .74rem; font-weight: 700; white-space: nowrap; display: none; z-index: 2; }
    .mcol:hover .mtip, .mcol:focus-visible .mtip { display: block; }
    .mlabels { display: flex; gap: 10px; margin-top: 10px; }
    .mlabels span { flex: 1; text-align: center; font-size: .7rem; color: var(--slate); }

    /* Upcoming list */
    .up-row { display: grid; grid-template-columns: minmax(0, 1.6fr) 150px 140px; gap: 20px; align-items: center; padding: 18px 0; border-top: 1px solid var(--line); }
    .up-list > .up-row:last-of-type { border-bottom: 1px solid var(--line); }
    .up-row .name { font-weight: 700; font-size: .92rem; }
    .up-row .date { font-size: .84rem; color: var(--slate); }
    .up-row .amt { text-align: right; font-weight: 700; }

    /* Cash flow */
    .flow { display: grid; grid-template-columns: 160px 1fr 140px; gap: 20px; align-items: center; padding: 16px 0; border-top: 1px solid var(--line); font-size: .88rem; }
    .flow:last-of-type { border-bottom: 1px solid var(--line); }
    .flow .bar { height: 6px; background: var(--line); border-radius: 3px; overflow: hidden; }
    .flow .bar b { display: block; height: 100%; border-radius: 3px; }
    .flow .amt { text-align: right; font-weight: 700; }
    .flow.net { font-weight: 700; }

    .empty { text-align: center; padding: 48px 16px; color: var(--slate); font-size: .88rem; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
    .empty strong { display: block; color: var(--ivory); font-size: .95rem; margin-bottom: 6px; }
    .note { font-size: .76rem; color: var(--slate); margin-top: 20px; max-width: 70ch; }

    @media (max-width: 980px) {
        .inc-hero { grid-template-columns: 1fr; gap: 32px; }
    }
    @media (max-width: 680px) {
        .inc .tabs { gap: 20px; }
        .mchart { height: 170px; gap: 5px; }
        .mlabels { gap: 5px; }
        .mlabels span { font-size: .6rem; }
        .up-row { grid-template-columns: 1fr auto; gap: 4px 12px; }
        .up-row .date { grid-column: 1; grid-row: 2; }
        .up-row .amt { grid-column: 2; grid-row: 1 / span 2; align-self: center; }
        .flow { grid-template-columns: 1fr auto; gap: 8px 12px; }
        .flow .bar { grid-column: 1 / -1; grid-row: 2; }
    }
</style>
@endpush

@section('content')
@php
    $fmt = fn($n) => '€' . number_format($n, 2);
    $year = date('Y');

    $bars = collect($chartData);
    $maxBar = (float) $bars->max('value');
    $topIndex = $maxBar > 0 ? $bars->search(fn($b) => (float) $b['value'] === $maxBar) : null;
    $hasIncome = $maxBar > 0;

    $upcoming = collect($upcomingDistributions);
    $upcomingTotal = (float) $upcoming->sum('payout');

    $income = (float) $totalIncome;
    $fees = (float) $totalFees;
    $net = $income - $fees;
    $balance = (float) ($user->balance ?? 0);
    $flowMax = max($income, $fees, 0.01);

    [$whole, $cents] = explode('.', number_format($income, 2));
@endphp

<div class="inc">
    <div class="tabs" data-panels="#income-panels">
        <button class="tab-btn active" data-tab="distributions">Distributions</button>
        <button class="tab-btn" data-tab="cashflow">Cash Flow</button>
    </div>

    <div id="income-panels">

        {{-- ================= DISTRIBUTIONS ================= --}}
        <div class="tab-panel active" id="distributions">
            <div class="inc-hero">
                <div>
                    <div class="eyebrow">Total income · {{ $year }}</div>
                    <div class="inc-total num">€{{ $whole }}<span class="cents">.{{ $cents }}</span></div>
                    <div class="muted" style="font-size:.88rem;">Distributions received so far this year.</div>
                </div>
                <div class="inc-metrics">
                    <div class="inc-metric">
                        <div class="eyebrow">Average monthly</div>
                        <div class="v num">{{ $fmt($averageMonthlyIncome) }}</div>
                    </div>
                    <div class="inc-metric">
                        <div class="eyebrow">Upcoming</div>
                        <div class="v num">{{ $fmt($upcomingTotal) }}</div>
                        <div class="sub">{{ $upcoming->count() }} scheduled {{ Str::plural('payout', $upcoming->count()) }}</div>
                    </div>
                    @if($hasIncome)
                    <div class="inc-metric">
                        <div class="eyebrow">Best month</div>
                        <div class="v num">{{ $fmt($maxBar) }}</div>
                        <div class="sub">{{ $bars[$topIndex]['label'] }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="inc-section">
                <div class="section-head">
                    <div>
                        <h2>Monthly income</h2>
                        <div class="lede">Distributions received each month of {{ $year }}.</div>
                    </div>
                </div>

                @if($hasIncome)
                    <div class="mchart" role="img" aria-label="Monthly income for {{ $year }}">
                        @foreach($bars as $i => $bar)
                            @php
                                $v = (float) $bar['value'];
                                $h = $maxBar > 0 ? round(($v / $maxBar) * 100, 1) : 0;
                            @endphp
                            <div class="mcol {{ $v <= 0 ? 'zero' : '' }} {{ $i === $topIndex ? 'top' : '' }}" tabindex="0" aria-label="{{ $bar['label'] }}: {{ $fmt($v) }}">
                                <span class="mtip num">{{ $bar['label'] }} · {{ $fmt($v) }}</span>
                                <div class="mbar" style="height: {{ $h }}%;"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mlabels">@foreach($bars as $bar)<span>{{ $bar['label'] }}</span>@endforeach</div>
                @else
                    <div class="empty"><strong>No income received yet in {{ $year }}</strong>Distributions will be charted here once your assets begin paying out.</div>
                @endif
            </div>

            <div class="inc-section">
                <h2>Upcoming distributions</h2>
                <div class="lede">Scheduled payouts. Amounts may change until the payment date.</div>

                <div class="up-list">
                    @forelse($upcoming as $dist)
                        <div class="up-row">
                            <div class="name">{{ $dist['asset_name'] }}</div>
                            <div class="date">{{ $dist['date'] }}</div>
                            <div class="amt num">{{ $fmt($dist['payout']) }}</div>
                        </div>
                    @empty
                        <div class="empty"><strong>No upcoming distributions</strong>When a payout is scheduled for one of your assets, it will appear here.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ================= CASH FLOW ================= --}}
        <div class="tab-panel" id="cashflow">
            <div class="inc-hero">
                <div>
                    <div class="eyebrow">Available balance</div>
                    @php [$bw, $bc] = explode('.', number_format($balance, 2)); @endphp
                    <div class="inc-total num">€{{ $bw }}<span class="cents">.{{ $bc }}</span></div>
                    <div class="muted" style="font-size:.88rem;">Cash ready to invest or withdraw.</div>
                </div>
                <div class="inc-metrics">
                    <div class="inc-metric">
                        <div class="eyebrow">Income · {{ $year }}</div>
                        <div class="v num positive">+{{ $fmt($income) }}</div>
                    </div>
                    <div class="inc-metric">
                        <div class="eyebrow">Fees · {{ $year }}</div>
                        <div class="v num negative">−{{ $fmt($fees) }}</div>
                    </div>
                </div>
            </div>

            <div class="inc-section">
                <h2>Income versus fees</h2>
                <div class="lede">What you earned from distributions against what you paid in fees this year.</div>

                @if($income > 0 || $fees > 0)
                    <div class="flow">
                        <span>Income</span>
                        <span class="bar"><b style="width: {{ round(($income / $flowMax) * 100, 1) }}%; background: var(--green);"></b></span>
                        <span class="amt num positive">+{{ $fmt($income) }}</span>
                    </div>
                    <div class="flow">
                        <span>Fees</span>
                        <span class="bar"><b style="width: {{ round(($fees / $flowMax) * 100, 1) }}%; background: var(--red);"></b></span>
                        <span class="amt num negative">−{{ $fmt($fees) }}</span>
                    </div>
                    <div class="flow net">
                        <span>Net</span>
                        <span></span>
                        <span class="amt num {{ $net >= 0 ? 'positive' : 'negative' }}">{{ $net >= 0 ? '+' : '−' }}{{ $fmt(abs($net)) }}</span>
                    </div>
                    <div class="note">Net is income minus fees for {{ $year }}. It does not include deposits, withdrawals or changes in asset value.</div>
                @else
                    <div class="empty"><strong>No cash movements yet in {{ $year }}</strong>Income and fees will be compared here once there is activity.</div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection