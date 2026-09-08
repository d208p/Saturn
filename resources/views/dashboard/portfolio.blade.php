@extends('layouts.app')
@section('title', 'Portfolio')

@section('content')
    @php
        // Dynamic calculations based on user holdings
        $holdings = $user->holdings ?? collect();
        $investedTotal = $holdings->sum('total_invested');
        
        // Compute current market value of all holdings
        $holdingsValue = $holdings->sum(function ($holding) {
            return $holding->shares_owned * ($holding->asset->share_price ?? 0);
        });
        
        $availableCash = $user->balance ?? 0;
        $portfolioValue = $holdingsValue + $availableCash;

        // Group holdings by category for dynamic allocation calculation
        $categoryHoldings = $holdings->groupBy(function ($holding) {
            return $holding->asset->category ?? 'Other';
        });
    @endphp

    <div class="tabs" data-panels="#portfolio-panels">
        <button class="tab-btn active" data-tab="overview">Overview</button>
        <button class="tab-btn" data-tab="holdings">Holdings</button>
        <button class="tab-btn" data-tab="allocation">Allocation</button>
        <button class="tab-btn" data-tab="performance">Performance</button>
    </div>

    <div id="portfolio-panels">

        <!-- Overview -->
        <div class="tab-panel active" id="overview">
            <div class="grid grid-3" style="margin-bottom: 24px;">
                <div class="card">
                    <div class="stat-label">Portfolio value</div>
                    <div class="stat-value">€{{ number_format($portfolioValue, 2) }}</div>
                </div>
                <div class="card">
                    <div class="stat-label">Invested</div>
                    <div class="stat-value">€{{ number_format($investedTotal, 2) }}</div>
                </div>
                <div class="card">
                    <div class="stat-label">Available cash</div>
                    <div class="stat-value">€{{ number_format($availableCash, 2) }}</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="section-title">Portfolio risk</div>
                    <span class="badge neutral">Moderate · 6.4 / 10</span>
                </div>
                <div class="list-row"><div class="primary">Diversification</div><div class="value positive">Good</div></div>
                <div class="list-row"><div class="primary">Liquidity</div><div class="value" style="color: var(--gold);">Moderate</div></div>
                <div class="list-row"><div class="primary">Asset concentration</div><div class="value positive">Low</div></div>
                <div class="list-row"><div class="primary">Geographic exposure</div><div class="value" style="color: var(--gold);">Moderate</div></div>
                <div class="list-row"><div class="primary">Development exposure</div><div class="value positive">Low</div></div>
                <div class="disclaimer-box">
                    This score reflects how your current positions are calculated to behave together — it is not a recommendation and does not guarantee any outcome.
                </div>
            </div>
        </div>

        <!-- Holdings -->
        <div class="tab-panel" id="holdings">
            <div class="card">
                <div class="card-header">
                    <div class="section-title">Your holdings</div>
                    <span class="muted" style="font-size: 0.82rem;">{{ $holdings->count() }} {{ Str::plural('position', $holdings->count()) }}</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Category</th>
                            <th>Shares</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holdings as $holding)
                            @php
                                $asset = $holding->asset;
                                $currentValue = $holding->shares_owned * ($asset->share_price ?? 0);
                            @endphp
                            <tr class="row-link" onclick="window.location.href='/asset/{{ $asset->id ?? '' }}'">
                                <td style="font-weight: 700;">{{ $asset->title ?? 'Unknown Asset' }}</td>
                                <td class="muted">{{ $asset->category ?? 'General' }}</td>
                                <td>{{ number_format($holding->shares_owned) }}</td>
                                <td>€{{ number_format($currentValue, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="muted" style="text-align: center; padding: 24px;">
                                    You don't own any equity positions yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Allocation -->
        <div class="tab-panel" id="allocation">
            <div class="grid grid-2">
                <div class="card">
                    <div class="section-title">By asset type</div>
                    @forelse($categoryHoldings as $category => $group)
                        @php
                            $catValue = $group->sum(fn($h) => $h->shares_owned * ($h->asset->share_price ?? 0));
                            $percentage = $holdingsValue > 0 ? round(($catValue / $holdingsValue) * 100) : 0;
                        @endphp
                        <div class="allocation-row">
                            <div class="top"><span>{{ $category }}</span><span>{{ $percentage }}%</span></div>
                            <div class="allocation-bar"><div class="allocation-fill" style="width: {{ $percentage }}%;"></div></div>
                        </div>
                    @empty
                        <p class="muted" style="font-size: 0.85rem;">No holdings available to calculate allocation.</p>
                    @endforelse
                </div>

                <div class="card">
                    <div class="section-title">Geographic allocation</div>
                    <div class="allocation-row">
                        <div class="top"><span>Romania</span><span>100%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 100%;"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance -->
        <div class="tab-panel" id="performance">
            <div class="card chart-card" style="margin-bottom: 20px;">
                <div class="chart-head">
                    <div class="section-title" style="margin-bottom: 0;">Portfolio performance</div>
                    <div style="display: flex; gap: 6px;">
                        <button class="chip" data-range="1m">1M</button>
                        <button class="chip" data-range="6m">6M</button>
                        <button class="chip active" data-range="1y">1Y</button>
                        <button class="chip" data-range="all">ALL</button>
                    </div>
                </div>
                <svg class="chart-svg" viewBox="0 0 600 220" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="chartFill2" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#C6A15B" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#C6A15B" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <path data-range="1m" fill="url(#chartFill2)" stroke="none" d="M0,150 L67,145 L134,155 L200,140 L267,130 L333,135 L400,120 L467,125 L533,110 L600,100 L600,220 L0,220 Z"/>
                    <path data-range="1m" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,150 L67,145 L134,155 L200,140 L267,130 L333,135 L400,120 L467,125 L533,110 L600,100"/>
                    <path data-range="6m" fill="url(#chartFill2)" stroke="none" d="M0,190 L67,170 L134,180 L200,150 L267,160 L333,120 L400,140 L467,100 L533,110 L600,80 L600,220 L0,220 Z"/>
                    <path data-range="6m" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,190 L67,170 L134,180 L200,150 L267,160 L333,120 L400,140 L467,100 L533,110 L600,80"/>
                    <path data-range="1y" fill="url(#chartFill2)" stroke="none" d="M0,200 L67,185 L134,190 L200,160 L267,170 L333,130 L400,150 L467,90 L533,110 L600,60 L600,220 L0,220 Z"/>
                    <path data-range="1y" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,200 L67,185 L134,190 L200,160 L267,170 L333,130 L400,150 L467,90 L533,110 L600,60"/>
                    <path data-range="all" fill="url(#chartFill2)" stroke="none" d="M0,205 L67,195 L134,200 L200,175 L267,180 L333,140 L400,155 L467,95 L533,120 L600,50 L600,220 L0,220 Z"/>
                    <path data-range="all" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,205 L67,195 L134,200 L200,175 L267,180 L333,140 L400,155 L467,95 L533,120 L600,50"/>
                </svg>
            </div>

            <div class="card">
                <div class="section-title">Returns by period</div>
                <table>
                    <thead><tr><th>Period</th><th>Return</th></tr></thead>
                    <tbody>
                        <tr><td>1 month</td><td class="positive">+0.0%</td></tr>
                        <tr><td>6 months</td><td class="positive">+0.0%</td></tr>
                        <tr><td>1 year</td><td class="positive">+0.0%</td></tr>
                        <tr><td>All time</td><td class="positive">+0.0%</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection