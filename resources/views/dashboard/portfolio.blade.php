@extends('layouts.app')
@section('title', 'Portfolio')

@section('content')
    <div class="tabs" data-panels="#portfolio-panels">
        <button class="tab-btn" data-tab="overview">Overview</button>
        <button class="tab-btn" data-tab="holdings">Holdings</button>
        <button class="tab-btn" data-tab="allocation">Allocation</button>
        <button class="tab-btn" data-tab="performance">Performance</button>
    </div>

    <div id="portfolio-panels">

        <!-- Overview -->
        <div class="tab-panel" id="overview">
            <div class="grid grid-3" style="margin-bottom: 24px;">
                <div class="card">
                    <div class="stat-label">Portfolio value</div>
                    <div class="stat-value">€42,850</div>
                </div>
                <div class="card">
                    <div class="stat-label">Invested</div>
                    <div class="stat-value">€38,200</div>
                </div>
                <div class="card">
                    <div class="stat-label">Available cash</div>
                    <div class="stat-value">€4,650</div>
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
                    <span class="muted" style="font-size: 0.82rem;">3 positions</span>
                </div>
                <table>
                    <thead>
                        <tr><th>Asset</th><th>Type</th><th>Value</th><th>Return</th><th>Yield</th></tr>
                    </thead>
                    <tbody>
                        <tr class="row-link" onclick="window.location.href='/asset'">
                            <td style="font-weight: 700;">Bucharest Hotel</td>
                            <td class="muted">Hospitality</td>
                            <td>€12,400</td>
                            <td class="positive">+14.2%</td>
                            <td>7.1%</td>
                        </tr>
                        <tr class="row-link" onclick="window.location.href='/asset'">
                            <td style="font-weight: 700;">Solar Farm #12</td>
                            <td class="muted">Energy</td>
                            <td>€9,800</td>
                            <td class="positive">+8.7%</td>
                            <td>6.4%</td>
                        </tr>
                        <tr class="row-link" onclick="window.location.href='/asset'">
                            <td style="font-weight: 700;">Tuscany Vineyard</td>
                            <td class="muted">Agriculture</td>
                            <td>€7,200</td>
                            <td class="positive">+17.3%</td>
                            <td>5.8%</td>
                        </tr>
                    </tbody>
                </table>
                <p class="muted" style="font-size: 0.78rem; margin-top: 16px;">
                    Rows link to the asset detail template — wire each to its own asset once real data is in place.
                </p>
            </div>
        </div>

        <!-- Allocation -->
        <div class="tab-panel" id="allocation">
            <div class="grid grid-2">
                <div class="card">
                    <div class="section-title">By asset type</div>
                    <div class="allocation-row">
                        <div class="top"><span>Real Estate</span><span>42%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 42%;"></div></div>
                    </div>
                    <div class="allocation-row">
                        <div class="top"><span>Energy</span><span>27%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 27%;"></div></div>
                    </div>
                    <div class="allocation-row">
                        <div class="top"><span>Agriculture</span><span>18%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 18%;"></div></div>
                    </div>
                    <div class="allocation-row">
                        <div class="top"><span>Industrial</span><span>9%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 9%;"></div></div>
                    </div>
                    <div class="allocation-row">
                        <div class="top"><span>Development</span><span>4%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 4%;"></div></div>
                    </div>
                </div>

                <div class="card">
                    <div class="section-title">Geographic allocation</div>
                    <div class="allocation-row">
                        <div class="top"><span>Romania</span><span>38%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 38%;"></div></div>
                    </div>
                    <div class="allocation-row">
                        <div class="top"><span>Italy</span><span>26%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 26%;"></div></div>
                    </div>
                    <div class="allocation-row">
                        <div class="top"><span>France</span><span>18%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 18%;"></div></div>
                    </div>
                    <div class="allocation-row">
                        <div class="top"><span>Spain</span><span>11%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 11%;"></div></div>
                    </div>
                    <div class="allocation-row">
                        <div class="top"><span>Other</span><span>7%</span></div>
                        <div class="allocation-bar"><div class="allocation-fill" style="width: 7%;"></div></div>
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
                        <tr><td>1 month</td><td class="positive">+2.1%</td></tr>
                        <tr><td>6 months</td><td class="positive">+6.8%</td></tr>
                        <tr><td>1 year</td><td class="positive">+11.1%</td></tr>
                        <tr><td>All time</td><td class="positive">+14.6%</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection