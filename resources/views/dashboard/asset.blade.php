@extends('layouts.app')

@section('title', 'Bucharest Grand Hotel')

@section('content')
    <a href="/portfolio#holdings" class="link-muted" style="margin-bottom: 20px; display: inline-flex;">
        <svg class="icon" style="width: 14px; height: 14px;"><use href="#icon-arrow-left"/></svg> Back to holdings
    </a>

    <div class="card-header" style="align-items: flex-start; margin-bottom: 24px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h2 style="font-size: 1.6rem; font-weight: 800; letter-spacing: -0.01em;">Bucharest Grand Hotel</h2>
                <button class="watch-toggle is-active"><svg class="icon"><use href="#icon-heart"/></svg></button>
            </div>
            <div class="muted" style="font-size: 0.9rem; margin-top: 4px;">Bucharest, Romania · Hospitality</div>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <span class="badge neutral">€12.4M asset value</span>
            <span class="badge neutral">7.1% target yield</span>
            <span class="badge positive">+14.2% total return</span>
            <span class="badge muted">4.8 years held</span>
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
                    <path data-range="1m" fill="url(#assetFill)" stroke="none" d="M0,140 L100,135 L200,142 L300,120 L400,128 L500,105 L600,95 L600,200 L0,200 Z"/>
                    <path data-range="1m" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,140 L100,135 L200,142 L300,120 L400,128 L500,105 L600,95"/>
                    <path data-range="6m" fill="url(#assetFill)" stroke="none" d="M0,170 L100,150 L200,160 L300,110 L400,125 L500,85 L600,70 L600,200 L0,200 Z"/>
                    <path data-range="6m" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,170 L100,150 L200,160 L300,110 L400,125 L500,85 L600,70"/>
                    <path data-range="1y" fill="url(#assetFill)" stroke="none" d="M0,185 L100,165 L200,175 L300,120 L400,140 L500,75 L600,50 L600,200 L0,200 Z"/>
                    <path data-range="1y" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,185 L100,165 L200,175 L300,120 L400,140 L500,75 L600,50"/>
                    <path data-range="all" fill="url(#assetFill)" stroke="none" d="M0,190 L100,178 L200,182 L300,140 L400,150 L500,65 L600,40 L600,200 L0,200 Z"/>
                    <path data-range="all" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,190 L100,178 L200,182 L300,140 L400,150 L500,65 L600,40"/>
                </svg>
            </div>

            <div class="card">
                <div class="section-title">About this asset</div>
                <p class="muted" style="font-size: 0.9rem;">
                    A 142-room operating hotel in central Bucharest, acquired in 2022 and run by an independent hospitality operator under a revenue-share agreement. Income is generated from room bookings and event space, distributed to investors quarterly.
                </p>
            </div>

            <div class="card">
                <div class="section-title">Asset performance</div>
                <div class="grid grid-2" style="gap: 16px; margin-bottom: 4px;">
                    <div class="list-row"><div class="primary">Revenue (TTM)</div><div class="value">€1.84M</div></div>
                    <div class="list-row"><div class="primary">Occupancy</div><div class="value">78%</div></div>
                    <div class="list-row"><div class="primary">Operating expenses</div><div class="value">€1.02M</div></div>
                    <div class="list-row"><div class="primary">NOI</div><div class="value positive">€820K</div></div>
                    <div class="list-row"><div class="primary">Debt (LTV)</div><div class="value">38%</div></div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Distribution history</div>
                <table>
                    <thead><tr><th>Date</th><th>Type</th><th>Amount / unit</th></tr></thead>
                    <tbody>
                        <tr><td>Jun 30, 2026</td><td class="muted">Quarterly distribution</td><td>€0.17</td></tr>
                        <tr><td>Mar 31, 2026</td><td class="muted">Quarterly distribution</td><td>€0.15</td></tr>
                        <tr><td>Dec 31, 2025</td><td class="muted">Quarterly distribution</td><td>€0.16</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right column: position + trade -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="section-title">Your position</div>
                <div class="list-row"><div class="primary">Units owned</div><div class="value">1,240</div></div>
                <div class="list-row"><div class="primary">Current value</div><div class="value">€12,400</div></div>
                <div class="list-row"><div class="primary">Avg. purchase price</div><div class="value">€8.90</div></div>
                <div class="list-row"><div class="primary">Current price</div><div class="value">€10.00</div></div>
                <div class="list-row"><div class="primary">Unrealized gain</div><div class="value positive">+€1,364</div></div>

                <div style="display: flex; gap: 10px; margin-top: 18px;">
                    <a href="/trade?side=buy" class="btn btn-gold btn-block">Buy</a>
                    <a href="/trade?side=sell" class="btn btn-outline btn-block">Sell</a>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Liquidity</div>
                <span class="badge positive" style="margin-bottom: 10px;">High</span>
                <p class="muted" style="font-size: 0.85rem;">€184K traded in the last 30 days on the secondary market.</p>
            </div>
        </div>
    </div>
@endsection