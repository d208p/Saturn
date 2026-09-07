@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $firstName = explode(' ', Auth::user()->name)[0];
    @endphp

    <div style="margin-bottom: 28px;">
        <div class="muted" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px;">
            {{ $greeting }}, {{ $firstName }}
        </div>
        <div style="display: flex; align-items: baseline; gap: 14px; flex-wrap: wrap;">
            <div style="font-size: 2.4rem; font-weight: 800; letter-spacing: -0.02em;">€42,850.00</div>
            <span class="badge positive">
                <svg class="icon" style="width:13px;height:13px;"><use href="#icon-arrow-up-right"/></svg>
                +€4,280.00 · +11.1%
            </span>
        </div>
        <div class="muted" style="font-size: 0.85rem; margin-top: 4px;">Total portfolio value</div>
    </div>

    <!-- Performance chart -->
    <div class="card chart-card" style="margin-bottom: 24px;">
        <div class="chart-head">
            <div>
                <div class="section-title" style="margin-bottom: 2px;">Portfolio performance</div>
                <div class="muted" style="font-size: 0.82rem;">Value over time, including distributions</div>
            </div>
            <div style="display: flex; gap: 6px;">
                <button class="chip" data-range="1m">1M</button>
                <button class="chip" data-range="6m">6M</button>
                <button class="chip active" data-range="1y">1Y</button>
                <button class="chip" data-range="all">ALL</button>
            </div>
        </div>

        <svg class="chart-svg" viewBox="0 0 600 220" preserveAspectRatio="none">
            <defs>
                <linearGradient id="chartFill" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#C6A15B" stop-opacity="0.35"/>
                    <stop offset="100%" stop-color="#C6A15B" stop-opacity="0"/>
                </linearGradient>
            </defs>

            <path data-range="1m" fill="url(#chartFill)" stroke="none" d="M0,150 L67,145 L134,155 L200,140 L267,130 L333,135 L400,120 L467,125 L533,110 L600,100 L600,220 L0,220 Z"/>
            <path data-range="1m" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,150 L67,145 L134,155 L200,140 L267,130 L333,135 L400,120 L467,125 L533,110 L600,100"/>

            <path data-range="6m" fill="url(#chartFill)" stroke="none" d="M0,190 L67,170 L134,180 L200,150 L267,160 L333,120 L400,140 L467,100 L533,110 L600,80 L600,220 L0,220 Z"/>
            <path data-range="6m" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,190 L67,170 L134,180 L200,150 L267,160 L333,120 L400,140 L467,100 L533,110 L600,80"/>

            <path data-range="1y" fill="url(#chartFill)" stroke="none" d="M0,200 L67,185 L134,190 L200,160 L267,170 L333,130 L400,150 L467,90 L533,110 L600,60 L600,220 L0,220 Z"/>
            <path data-range="1y" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,200 L67,185 L134,190 L200,160 L267,170 L333,130 L400,150 L467,90 L533,110 L600,60"/>

            <path data-range="all" fill="url(#chartFill)" stroke="none" d="M0,205 L67,195 L134,200 L200,175 L267,180 L333,140 L400,155 L467,95 L533,120 L600,50 L600,220 L0,220 Z"/>
            <path data-range="all" fill="none" stroke="#C6A15B" stroke-width="2.5" d="M0,205 L67,195 L134,200 L200,175 L267,180 L333,140 L400,155 L467,95 L533,120 L600,50"/>
        </svg>
    </div>

    <!-- Key stats -->
    <div class="grid grid-3" style="margin-bottom: 24px;">
        <div class="card">
            <div class="stat-label">Invested</div>
            <div class="stat-value">€38,200</div>
        </div>
        <div class="card">
            <div class="stat-label">Available cash</div>
            <div class="stat-value">€4,650</div>
        </div>
        <div class="card">
            <div class="stat-label">Unrealized gain</div>
            <div class="stat-value positive">+€3,064</div>
        </div>
        <div class="card">
            <div class="stat-label">Income received</div>
            <div class="stat-value">€1,216.70</div>
        </div>
        <div class="card">
            <div class="stat-label">Portfolio yield</div>
            <div class="stat-value">6.8%</div>
        </div>
        <div class="card">
            <div class="stat-label">Next distribution</div>
            <div class="stat-value" style="font-size: 1.05rem;">€184.20 <span class="muted" style="font-size: 0.78rem; font-weight: 500;">· Oct 1</span></div>
        </div>
    </div>

    <div class="grid grid-2">
        <!-- Holdings preview -->
        <div class="card">
            <div class="card-header">
                <div class="section-title">Your holdings</div>
                <a href="/portfolio#holdings" class="link-muted">View all <svg class="icon" style="width:14px;height:14px;"><use href="#icon-chevron-right"/></svg></a>
            </div>
            <div class="list-row">
                <div>
                    <div class="primary">Bucharest Hotel</div>
                    <div class="secondary">Hospitality · +14.2%</div>
                </div>
                <div class="value">€12,400</div>
            </div>
            <div class="list-row">
                <div>
                    <div class="primary">Solar Farm #12</div>
                    <div class="secondary">Energy · +8.7%</div>
                </div>
                <div class="value">€9,800</div>
            </div>
            <div class="list-row">
                <div>
                    <div class="primary">Tuscany Vineyard</div>
                    <div class="secondary">Agriculture · +17.3%</div>
                </div>
                <div class="value">€7,200</div>
            </div>
        </div>

        <!-- Income + Market teaser -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <div class="section-title">Income</div>
                    <a href="/income#cashflow" class="link-muted">Details <svg class="icon" style="width:14px;height:14px;"><use href="#icon-chevron-right"/></svg></a>
                </div>
                <div class="grid grid-2" style="gap: 16px;">
                    <div>
                        <div class="stat-label">This month</div>
                        <div class="stat-value">€223.40</div>
                    </div>
                    <div>
                        <div class="stat-label">2026 total</div>
                        <div class="stat-value">€1,216.70</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Market</div>
                <p class="muted" style="font-size: 0.88rem; margin-bottom: 16px;">
                    3 new opportunities · 12 positions available on the secondary market.
                </p>
                <a href="/market#secondary" class="btn btn-gold btn-block">Explore market</a>
            </div>
        </div>
    </div>
@endsection