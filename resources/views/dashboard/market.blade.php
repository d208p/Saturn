@extends('layouts.app')

@section('title', 'Market')

@push('styles')
<style>
    .project-card, .listing-card { border-bottom: 1px solid var(--card-border); padding: 20px 0; }
    .project-card:last-child, .listing-card:last-child { border-bottom: none; }
    .listing-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
    .trend-card { padding: 20px; }
</style>
@endpush

@section('content')
    <div class="tabs" data-panels="#market-panels">
        <button class="tab-btn" data-tab="discover">Discover</button>
        <button class="tab-btn" data-tab="new-projects">New Projects</button>
        <button class="tab-btn" data-tab="secondary">Secondary Market</button>
        <button class="tab-btn" data-tab="watchlist">Watchlist</button>
        <button class="tab-btn" data-tab="my-listings">My Listings</button>
    </div>

    <div id="market-panels">

        <!-- Discover -->
        <div class="tab-panel" id="discover">
            <div class="section-title">Trending categories</div>
            <div class="grid grid-3">
                <div class="card trend-card">
                    <div class="stat-label">Hospitality</div>
                    <div style="font-weight: 700; margin: 4px 0 6px;">Hotel positions</div>
                    <p class="muted" style="font-size: 0.85rem;">Operating hotels with established occupancy and revenue history.</p>
                </div>
                <div class="card trend-card">
                    <div class="stat-label">Energy</div>
                    <div style="font-weight: 700; margin: 4px 0 6px;">Solar & wind</div>
                    <p class="muted" style="font-size: 0.85rem;">Long-term power purchase agreements with predictable output.</p>
                </div>
                <div class="card trend-card">
                    <div class="stat-label">Agriculture</div>
                    <div style="font-weight: 700; margin: 4px 0 6px;">Vineyards & orchards</div>
                    <p class="muted" style="font-size: 0.85rem;">Land-backed assets with seasonal, harvest-linked cash flow.</p>
                </div>
                <div class="card trend-card">
                    <div class="stat-label">Development</div>
                    <div style="font-weight: 700; margin: 4px 0 6px;">Ground-up projects</div>
                    <p class="muted" style="font-size: 0.85rem;">Higher-risk, higher-upside projects still under construction.</p>
                </div>
                <div class="card trend-card">
                    <div class="stat-label">Infrastructure</div>
                    <div style="font-weight: 700; margin: 4px 0 6px;">Logistics & utilities</div>
                    <p class="muted" style="font-size: 0.85rem;">Essential-service assets, typically lower volatility.</p>
                </div>
                <div class="card trend-card" style="display: flex; flex-direction: column; justify-content: center; align-items: flex-start;">
                    <p class="muted" style="font-size: 0.85rem; margin-bottom: 12px;">Not sure where to start?</p>
                    <a href="#secondary" class="btn btn-outline btn-sm">Browse all listings</a>
                </div>
            </div>
        </div>

        <!-- New Projects (V1 style: fundraising) -->
        <div class="tab-panel" id="new-projects">
            <div class="filter-bar">
                <span class="filter-group-label">Asset type</span>
                <button class="filter-chip active">All</button>
                <button class="filter-chip">Real Estate</button>
                <button class="filter-chip">Energy</button>
                <button class="filter-chip">Agriculture</button>
                <button class="filter-chip">Industrial</button>
            </div>

            <div class="card">
                <div class="project-card">
                    <div class="listing-head">
                        <div>
                            <div style="font-weight: 700; font-size: 1rem;">Solar Farm #12</div>
                            <div class="muted" style="font-size: 0.82rem;">Dobrogea, Romania · Energy</div>
                        </div>
                        <span class="badge neutral">Medium risk</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 4px;">
                        <span class="muted">Raised €1,420,000 of €2,000,000</span>
                        <span style="font-weight: 700;">71%</span>
                    </div>
                    <div class="progress-track"><div class="progress-fill" style="width: 71%;"></div></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px;">
                        <div style="display: flex; gap: 20px;">
                            <div><div class="stat-label" style="margin-bottom: 2px;">Target yield</div><div style="font-weight: 700;">7.4%</div></div>
                            <div><div class="stat-label" style="margin-bottom: 2px;">Remaining</div><div style="font-weight: 700;">€580,000</div></div>
                        </div>
                        <a href="/trade" class="btn btn-gold btn-sm">Invest</a>
                    </div>
                </div>

                <div class="project-card">
                    <div class="listing-head">
                        <div>
                            <div style="font-weight: 700; font-size: 1rem;">Logistics Center #03</div>
                            <div class="muted" style="font-size: 0.82rem;">Cluj, Romania · Industrial</div>
                        </div>
                        <span class="badge negative">Higher risk</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 4px;">
                        <span class="muted">Raised €640,000 of €1,500,000</span>
                        <span style="font-weight: 700;">43%</span>
                    </div>
                    <div class="progress-track"><div class="progress-fill" style="width: 43%;"></div></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px;">
                        <div style="display: flex; gap: 20px;">
                            <div><div class="stat-label" style="margin-bottom: 2px;">Target yield</div><div style="font-weight: 700;">9.2%</div></div>
                            <div><div class="stat-label" style="margin-bottom: 2px;">Remaining</div><div style="font-weight: 700;">€860,000</div></div>
                        </div>
                        <a href="/trade" class="btn btn-gold btn-sm">Invest</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Market (V2: existing positions) -->
        <div class="tab-panel" id="secondary">
            <div class="grid grid-3" style="margin-bottom: 20px;">
                <div class="card">
                    <div class="stat-label">Total traded</div>
                    <div class="stat-value">€2.4M</div>
                </div>
                <div class="card">
                    <div class="stat-label">Transactions</div>
                    <div class="stat-value">1,842</div>
                </div>
                <div class="card">
                    <div class="stat-label">Avg. position price</div>
                    <div class="stat-value">€126 <span class="badge positive" style="margin-left: 6px;">High liquidity</span></div>
                </div>
            </div>

            <div class="filter-bar">
                <span class="filter-group-label">Yield</span>
                <button class="filter-chip active">All</button>
                <button class="filter-chip">0–5%</button>
                <button class="filter-chip">5–8%</button>
                <button class="filter-chip">8–12%</button>
                <button class="filter-chip">12%+</button>
                <span class="filter-group-label" style="margin-left: 12px;">Risk</span>
                <button class="filter-chip active">All</button>
                <button class="filter-chip">Low</button>
                <button class="filter-chip">Medium</button>
                <button class="filter-chip">High</button>
            </div>

            <div class="card">
                <div class="listing-card">
                    <div class="listing-head">
                        <div>
                            <div style="font-weight: 700; font-size: 1rem;">Solar Farm #12</div>
                            <div class="muted" style="font-size: 0.82rem;">Market price €112.40 / unit · 420 units available</div>
                        </div>
                        <button class="watch-toggle"><svg class="icon"><use href="#icon-heart"/></svg></button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; gap: 20px;">
                            <div><div class="stat-label" style="margin-bottom: 2px;">Yield</div><div style="font-weight: 700;">7.1%</div></div>
                            <div><div class="stat-label" style="margin-bottom: 2px;">Risk</div><div style="font-weight: 700;">Medium</div></div>
                            <div><div class="stat-label" style="margin-bottom: 2px;">Liquidity</div><div style="font-weight: 700;" class="positive">High</div></div>
                        </div>
                        <a href="/trade" class="btn btn-gold btn-sm">Buy</a>
                    </div>
                </div>

                <div class="listing-card">
                    <div class="listing-head">
                        <div>
                            <div style="font-weight: 700; font-size: 1rem;">Bucharest Hotel</div>
                            <div class="muted" style="font-size: 0.82rem;">Market price €10.00 / unit · 180 units available</div>
                        </div>
                        <button class="watch-toggle"><svg class="icon"><use href="#icon-heart"/></svg></button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; gap: 20px;">
                            <div><div class="stat-label" style="margin-bottom: 2px;">Yield</div><div style="font-weight: 700;">7.1%</div></div>
                            <div><div class="stat-label" style="margin-bottom: 2px;">Risk</div><div style="font-weight: 700;">Medium</div></div>
                            <div><div class="stat-label" style="margin-bottom: 2px;">Liquidity</div><div style="font-weight: 700;" class="muted">Moderate</div></div>
                        </div>
                        <a href="/trade" class="btn btn-gold btn-sm">Buy</a>
                    </div>
                </div>

                <div class="listing-card">
                    <div class="listing-head">
                        <div>
                            <div style="font-weight: 700; font-size: 1rem;">Tuscany Vineyard</div>
                            <div class="muted" style="font-size: 0.82rem;">Market price €58.20 / unit · 90 units available</div>
                        </div>
                        <button class="watch-toggle"><svg class="icon"><use href="#icon-heart"/></svg></button>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; gap: 20px;">
                            <div><div class="stat-label" style="margin-bottom: 2px;">Yield</div><div style="font-weight: 700;">5.8%</div></div>
                            <div><div class="stat-label" style="margin-bottom: 2px;">Risk</div><div style="font-weight: 700;">Medium</div></div>
                            <div><div class="stat-label" style="margin-bottom: 2px;">Liquidity</div><div style="font-weight: 700;" class="negative">Low</div></div>
                        </div>
                        <a href="/trade" class="btn btn-gold btn-sm">Buy</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Watchlist -->
        <div class="tab-panel" id="watchlist">
            <div class="card">
                <div class="listing-card">
                    <div class="listing-head">
                        <div>
                            <div style="font-weight: 700;">Bucharest Hotel</div>
                            <div class="muted" style="font-size: 0.82rem;">€10.00 / unit</div>
                        </div>
                        <button class="watch-toggle is-active"><svg class="icon"><use href="#icon-heart"/></svg></button>
                    </div>
                    <span class="badge positive">+2.1% this week</span>
                </div>
                <div class="listing-card">
                    <div class="listing-head">
                        <div>
                            <div style="font-weight: 700;">Tuscany Vineyard</div>
                            <div class="muted" style="font-size: 0.82rem;">€58.20 / unit</div>
                        </div>
                        <button class="watch-toggle is-active"><svg class="icon"><use href="#icon-heart"/></svg></button>
                    </div>
                    <span class="badge negative">-0.6% this week</span>
                </div>
                <div class="listing-card">
                    <div class="listing-head">
                        <div>
                            <div style="font-weight: 700;">Logistics Center #03</div>
                            <div class="muted" style="font-size: 0.82rem;">Fundraising · 43% funded</div>
                        </div>
                        <button class="watch-toggle is-active"><svg class="icon"><use href="#icon-heart"/></svg></button>
                    </div>
                    <span class="badge neutral">New listing</span>
                </div>
            </div>
        </div>

        <!-- My Listings -->
        <div class="tab-panel" id="my-listings">
            <div class="card">
                <table>
                    <thead><tr><th>Asset</th><th>Units listed</th><th>Ask price</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 700;">Tuscany Vineyard</td>
                            <td>300</td>
                            <td>€112.40</td>
                            <td><span class="badge positive">Active</span></td>
                            <td><button class="btn btn-danger-outline btn-sm">Cancel</button></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">Solar Farm #12</td>
                            <td>150</td>
                            <td>€110.00</td>
                            <td><span class="badge muted">Filled</span></td>
                            <td class="muted" style="font-size: 0.82rem;">Sep 3</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection