@extends('layouts.app')

@section('title', 'Income')

@push('styles')
<style>
    .bar-chart { display: flex; align-items: flex-end; gap: 14px; height: 160px; margin: 20px 0 8px; }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; }
    .bar { width: 100%; max-width: 36px; background: var(--gold); border-radius: 5px 5px 0 0; }
    .bar-label { font-size: 0.72rem; color: var(--slate); margin-top: 8px; }
    .bar-value { font-size: 0.72rem; color: var(--ivory); font-weight: 700; margin-bottom: 6px; }
</style>
@endpush

@section('content')
    <div class="tabs" data-panels="#income-panels">
        <button class="tab-btn active" data-tab="distributions">Distributions</button>
        <button class="tab-btn" data-tab="cashflow">Cash Flow</button>
    </div>

    <div id="income-panels">

        <!-- Distributions -->
        <div class="tab-panel" id="distributions">
            <div class="grid grid-2" style="margin-bottom: 20px;">
                <div class="card">
                    <div class="stat-label">Total income ({{ date('Y') }})</div>
                    <div class="stat-value">€{{ number_format($totalIncome, 2) }}</div>
                </div>
                <div class="card">
                    <div class="stat-label">Average monthly income</div>
                    <div class="stat-value">€{{ number_format($averageMonthlyIncome, 2) }}</div>
                </div>
            </div>

            <div class="card" style="margin-bottom: 20px;">
                <div class="section-title">Monthly income · {{ date('Y') }}</div>
                <div class="bar-chart">
                    @foreach($chartData as $bar)
                        <div class="bar-col">
                            <span class="bar-value">€{{ number_format($bar['value'], 0) }}</span>
                            <div class="bar" style="height: {{ $bar['height'] }}%;"></div>
                            <span class="bar-label">{{ $bar['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="section-title">Upcoming distributions</div>
                @forelse($upcomingDistributions as $dist)
                    <div class="list-row">
                        <div>
                            <div class="primary">{{ $dist['asset_name'] }}</div>
                            <div class="secondary">Scheduled payout · {{ $dist['date'] }}</div>
                        </div>
                        <div class="value">€{{ number_format($dist['payout'], 2) }}</div>
                    </div>
                @empty
                    <div class="muted" style="padding: 10px 0;">No upcoming distributions scheduled.</div>
                @endforelse
            </div>
        </div>

        <!-- Cash Flow -->
        <div class="tab-panel" id="cashflow">
            <div class="grid grid-3" style="margin-bottom: 20px;">
                <div class="card">
                    <div class="stat-label">Available balance</div>
                    <div class="stat-value">€{{ number_format($user->balance ?? 0, 2) }}</div>
                </div>
                <div class="card">
                    <div class="stat-label">Income ({{ date('Y') }})</div>
                    <div class="stat-value positive">+€{{ number_format($totalIncome, 2) }}</div>
                </div>
                <div class="card">
                    <div class="stat-label">Fees ({{ date('Y') }})</div>
                    <div class="stat-value negative">-€0.00</div>
                </div>
            </div>
        </div>

    </div>
@endsection