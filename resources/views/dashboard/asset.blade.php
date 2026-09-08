@extends('layouts.app')

@section('title', $asset->title)

@section('content')
    <a href="/portfolio#holdings" class="link-muted" style="margin-bottom: 20px; display: inline-flex;">
        <svg class="icon" style="width: 14px; height: 14px;"><use href="#icon-arrow-left"/></svg> Back to holdings
    </a>

    <div class="card-header" style="align-items: flex-start; margin-bottom: 24px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h2 style="font-size: 1.6rem; font-weight: 800; letter-spacing: -0.01em;">{{ $asset->title }}</h2>
                <button class="watch-toggle is-active"><svg class="icon"><use href="#icon-heart"/></svg></button>
            </div>
            <div class="muted" style="font-size: 0.9rem; margin-top: 4px;">{{ $asset->location ?? 'Global' }} · {{ $asset->category ?? 'Real Estate' }}</div>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <span class="badge neutral">€{{ number_format($asset->total_valuation ?? 0) }} asset value</span>
            <span class="badge neutral">{{ number_format($asset->target_yield ?? 0, 1) }}% target yield</span>
            <span class="badge positive">+{{ number_format($asset->total_return ?? 0, 1) }}% total return</span>
        </div>
    </div>

    <div class="grid grid-2" style="align-items: start; grid-template-columns: 2fr 1fr;">
        <!-- Left column -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card chart-card">
                <div class="chart-head">
                    <div class="section-title" style="margin-bottom: 0;">Unit price</div>
                    <div style="display: flex; gap: 6px;">
                        <button class="chip" data-range="1m">1M</button>
                        <button class="chip" data-range="6m">6M</button>
                        <button class="chip active" data-range="1y">1Y</button>
                        <button class="chip" data-range="all">ALL</button>
                    </div>
                </div>
                <svg class="chart-svg" viewBox="0 0 600 200" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="assetFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#C6A15B" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#C6A15B" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <path data-range="1y" fill="url(#assetFill)" stroke="none" d="M0,185 L100,165 L200,175 L300,120 L400,140 L500,75 L600,50 L600,200 L0,200 Z"/>
                    <path data-range="1y" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,185 L100,165 L200,175 L300,120 L400,140 L500,75 L600,50"/>
                </svg>
            </div>

            <div class="card">
                <div class="section-title">About this asset</div>
                <p class="muted" style="font-size: 0.9rem;">
                    {{ $asset->description }}
                </p>
            </div>

            <div class="card">
                <div class="section-title">Asset performance</div>
                <div class="grid grid-2" style="gap: 16px; margin-bottom: 4px;">
                    <div class="list-row"><div class="primary">Revenue (TTM)</div><div class="value">€{{ number_format($asset->revenue_ttm ?? 0) }}</div></div>
                    <div class="list-row"><div class="primary">Occupancy</div><div class="value">{{ $asset->occupancy_rate ?? 0 }}%</div></div>
                    <div class="list-row"><div class="primary">NOI</div><div class="value positive">€{{ number_format($asset->noi ?? 0) }}</div></div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Distribution history</div>
                <table>
                    <thead><tr><th>Date</th><th>Type</th><th>Amount / unit</th></tr></thead>
                    <tbody>
                        @forelse($distributions as $dist)
                            <tr>
                                <td>{{ $dist->created_at->format('M d, Y') }}</td>
                                <td class="muted">Quarterly distribution</td>
                                <td>€{{ number_format($dist->amount_per_share, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="muted">No distribution history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right column: position + trade -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="section-title">Your position</div>
                <div class="list-row"><div class="primary">Units owned</div><div class="value">{{ number_format($unitsOwned) }}</div></div>
                <div class="list-row"><div class="primary">Current value</div><div class="value">€{{ number_format($currentValue, 2) }}</div></div>
                <div class="list-row"><div class="primary">Avg. purchase price</div><div class="value">€{{ number_format($avgPurchasePrice, 2) }}</div></div>
                <div class="list-row"><div class="primary">Current price</div><div class="value">€{{ number_format($asset->share_price, 2) }}</div></div>
                <div class="list-row">
                    <div class="primary">Unrealized gain</div>
                    <div class="value {{ $unrealizedGain >= 0 ? 'positive' : 'negative' }}">
                        {{ $unrealizedGain >= 0 ? '+' : '' }}€{{ number_format($unrealizedGain, 2) }}
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 18px;">
                    <a href="{{ route('trade.show', $asset->id) }}?side=buy" class="btn btn-gold btn-block">Buy</a>
                    <a href="{{ route('trade.show', $asset->id) }}?side=sell" class="btn btn-outline btn-block">Sell</a>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Liquidity</div>
                <span class="badge positive" style="margin-bottom: 10px;">High</span>
                <p class="muted" style="font-size: 0.85rem;">Available units on secondary market: {{ number_format($asset->available_shares) }}</p>
            </div>
        </div>
    </div>
@endsection