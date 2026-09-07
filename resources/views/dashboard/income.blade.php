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
        <button class="tab-btn" data-tab="distributions">Distributions</button>
        <button class="tab-btn" data-tab="cashflow">Cash Flow</button>
    </div>

    <div id="income-panels">

        <!-- Distributions -->
        <div class="tab-panel" id="distributions">
            <div class="grid grid-2" style="margin-bottom: 20px;">
                <div class="card">
                    <div class="stat-label">Total income (2026)</div>
                    <div class="stat-value">€1,216.70</div>
                </div>
                <div class="card">
                    <div class="stat-label">Average monthly income</div>
                    <div class="stat-value">€202.67</div>
                </div>
            </div>

            <div class="card" style="margin-bottom: 20px;">
                <div class="section-title">Monthly income · 2026</div>
                <div class="bar-chart">
                    <div class="bar-col"><span class="bar-value">€184</span><div class="bar" style="height: 80%;"></div><span class="bar-label">JAN</span></div>
                    <div class="bar-col"><span class="bar-value">€191</span><div class="bar" style="height: 83%;"></div><span class="bar-label">FEB</span></div>
                    <div class="bar-col"><span class="bar-value">€204</span><div class="bar" style="height: 89%;"></div><span class="bar-label">MAR</span></div>
                    <div class="bar-col"><span class="bar-value">€198</span><div class="bar" style="height: 86%;"></div><span class="bar-label">APR</span></div>
                    <div class="bar-col"><span class="bar-value">€216</span><div class="bar" style="height: 94%;"></div><span class="bar-label">MAY</span></div>
                    <div class="bar-col"><span class="bar-value">€223</span><div class="bar" style="height: 97%;"></div><span class="bar-label">JUN</span></div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Upcoming distributions</div>
                <div class="list-row">
                    <div><div class="primary">Bucharest Hotel</div><div class="secondary">Quarterly distribution · Oct 1, 2026</div></div>
                    <div class="value">€184.20</div>
                </div>
                <div class="list-row">
                    <div><div class="primary">Solar Farm #12</div><div class="secondary">Monthly distribution · Oct 5, 2026</div></div>
                    <div class="value">€61.30</div>
                </div>
                <div class="list-row">
                    <div><div class="primary">Tuscany Vineyard</div><div class="secondary">Annual distribution · Nov 15, 2026</div></div>
                    <div class="value">€94.00</div>
                </div>
            </div>
        </div>

        <!-- Cash Flow -->
        <div class="tab-panel" id="cashflow">
            <div class="grid grid-3" style="margin-bottom: 20px;">
                <div class="card">
                    <div class="stat-label">Available cash</div>
                    <div class="stat-value">€4,650</div>
                </div>
                <div class="card">
                    <div class="stat-label">Income (2026)</div>
                    <div class="stat-value positive">+€1,216.70</div>
                </div>
                <div class="card">
                    <div class="stat-label">Fees (2026)</div>
                    <div class="stat-value negative">-€48.20</div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Cash flow by month</div>
                <table>
                    <thead><tr><th>Month</th><th>Income</th><th>Fees</th><th>Net</th></tr></thead>
                    <tbody>
                        <tr><td>Jan</td><td class="positive">+€184.00</td><td class="negative">-€8.10</td><td>€175.90</td></tr>
                        <tr><td>Feb</td><td class="positive">+€191.00</td><td class="negative">-€8.10</td><td>€182.90</td></tr>
                        <tr><td>Mar</td><td class="positive">+€204.00</td><td class="negative">-€8.00</td><td>€196.00</td></tr>
                        <tr><td>Apr</td><td class="positive">+€198.00</td><td class="negative">-€8.00</td><td>€190.00</td></tr>
                        <tr><td>May</td><td class="positive">+€216.00</td><td class="negative">-€8.00</td><td>€208.00</td></tr>
                        <tr><td>Jun</td><td class="positive">+€223.00</td><td class="negative">-€8.00</td><td>€215.00</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection