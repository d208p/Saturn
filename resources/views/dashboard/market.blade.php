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
        <button class="tab-btn active" data-tab="discover">Discover</button>
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
            </div>
        </div>

        <!-- New Projects -->
        <div class="tab-panel" id="new-projects">
            <div class="card">
                @forelse($newProjects as $project)
                    @php
                        $totalValuation = $project->total_valuation ?? 0;
                        $fundedAmount = ($project->total_shares - $project->available_shares) * $project->share_price;
                        $progress = $totalValuation > 0 ? min(100, round(($fundedAmount / $totalValuation) * 100)) : 0;
                    @endphp
                    <div class="project-card">
                        <div class="listing-head">
                            <div>
                                <div style="font-weight: 700; font-size: 1rem;">{{ $project->title }}</div>
                                <div class="muted" style="font-size: 0.82rem;">{{ $project->category }}</div>
                            </div>
                            <span class="badge neutral">{{ ucfirst($project->status) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 4px;">
                            <span class="muted">Raised €{{ number_format($fundedAmount, 2) }} of €{{ number_format($totalValuation, 2) }}</span>
                            <span style="font-weight: 700;">{{ $progress }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $progress }}%;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px;">
                            <div style="display: flex; gap: 20px;">
                                <div>
                                    <div class="stat-label" style="margin-bottom: 2px;">Share Price</div>
                                    <div style="font-weight: 700;">€{{ number_format($project->share_price, 2) }}</div>
                                </div>
                                <div>
                                    <div class="stat-label" style="margin-bottom: 2px;">Available Shares</div>
                                    <div style="font-weight: 700;">{{ number_format($project->available_shares) }}</div>
                                </div>
                            </div>
                            <a href="{{ route('trade.show', $project->id) }}" class="btn btn-gold btn-sm">Invest</a>
                        </div>
                    </div>
                @empty
                    <div style="padding: 20px; text-align: center;" class="muted">No new projects available.</div>
                @endforelse
            </div>
        </div>

        <!-- Secondary Market -->
<div class="tab-panel" id="secondary">
    <div class="card">
        @forelse($secondaryAssets as $order)
            <div class="listing-card">
                <div class="listing-head">
                    <div>
                        <div style="font-weight: 700; font-size: 1rem;">{{ $order->asset->title ?? 'Asset N/A' }}</div>
                        <div class="muted" style="font-size: 0.82rem;">
                            Seller: {{ $order->user->name ?? 'User #'.$order->user_id }} · Category: {{ $order->asset->category ?? 'General' }}
                        </div>
                    </div>
                    <span class="badge neutral">Secondary Order</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                    <div style="display: flex; gap: 20px;">
                        <div>
                            <div class="stat-label" style="margin-bottom: 2px;">Ask Price</div>
                            <div style="font-weight: 700;">€{{ number_format($order->price_per_share, 2) }} / share</div>
                        </div>
                        <div>
                            <div class="stat-label" style="margin-bottom: 2px;">Units Offered</div>
                            <div style="font-weight: 700;">{{ number_format($order->shares) }}</div>
                        </div>
                    </div>
                    <a href="{{ route('trade.show', $order->asset_id) }}" class="btn btn-gold btn-sm">Buy Order</a>
                </div>
            </div>
        @empty
            <div style="padding: 20px; text-align: center;" class="muted">No active secondary market listings available.</div>
        @endforelse
    </div>
</div>

        <!-- Watchlist -->
        <div class="tab-panel" id="watchlist">
            <div class="card">
                @forelse($watchlist as $item)
                    <div class="listing-card">
                        <div class="listing-head">
                            <div>
                                <div style="font-weight: 700;">{{ $item->title }}</div>
                                <div class="muted" style="font-size: 0.82rem;">€{{ number_format($item->share_price, 2) }} / unit</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="padding: 20px; text-align: center;" class="muted">Your watchlist is currently empty.</div>
                @endforelse
            </div>
        </div>

        <!-- My Listings -->
<div class="tab-panel" id="my-listings">
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Asset</th>
                    <th>Units Listed</th>
                    <th>Ask Price</th>
                    <th>Date Listed</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($userListings as $listing)
                    <tr>
                        <td style="font-weight: 700;">{{ $listing->asset->title ?? 'N/A' }}</td>
                        <td>{{ number_format($listing->shares) }}</td>
                        <td>€{{ number_format($listing->price_per_share, 2) }}</td>
                        <td>{{ $listing->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($listing->status === 'active')
                                <span class="badge positive">Active</span>
                            @elseif($listing->status === 'filled')
                                <span class="badge neutral">Filled</span>
                            @else
                                <span class="badge negative">{{ ucfirst($listing->status) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted" style="text-align: center; padding: 20px;">
                            You have no active or previous listings.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

    </div>
@endsection