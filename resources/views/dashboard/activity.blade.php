@extends('layouts.app')

@section('title', 'Activity')

@section('content')
    <div class="tabs" data-panels="#activity-panels">
        <button class="tab-btn" data-tab="transactions">Transactions</button>
        <button class="tab-btn" data-tab="orders">Orders</button>
    </div>

    <div id="activity-panels">

        <!-- Transactions -->
        <div class="tab-panel" id="transactions">
            <div class="card">
                <div class="list-row">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="badge negative">BUY</span>
                        <div>
                            <div class="primary">Solar Farm #12</div>
                            <div class="secondary">Sep 4, 2026 · 200 units</div>
                        </div>
                    </div>
                    <div class="value negative">-€22,480</div>
                </div>
                <div class="list-row">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="badge positive">DISTRIBUTION</span>
                        <div>
                            <div class="primary">Bucharest Hotel</div>
                            <div class="secondary">Aug 28, 2026</div>
                        </div>
                    </div>
                    <div class="value positive">+€184.20</div>
                </div>
                <div class="list-row">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="badge positive">SELL</span>
                        <div>
                            <div class="primary">Tuscany Vineyard</div>
                            <div class="secondary">Aug 17, 2026 · 100 units</div>
                        </div>
                    </div>
                    <div class="value positive">+€1,126</div>
                </div>
                <div class="list-row">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="badge muted">FEE</span>
                        <div>
                            <div class="primary">Platform fee</div>
                            <div class="secondary">Aug 17, 2026</div>
                        </div>
                    </div>
                    <div class="value negative">-€8.10</div>
                </div>
                <div class="list-row">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="badge neutral">DEPOSIT</span>
                        <div>
                            <div class="primary">Bank transfer</div>
                            <div class="secondary">Aug 2, 2026</div>
                        </div>
                    </div>
                    <div class="value positive">+€5,000</div>
                </div>
            </div>
        </div>

        <!-- Orders -->
        <div class="tab-panel" id="orders">
            <div class="card">
                <table>
                    <thead><tr><th>Asset</th><th>Side</th><th>Units</th><th>Price</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 700;">Tuscany Vineyard</td>
                            <td><span class="badge positive">SELL</span></td>
                            <td>300</td>
                            <td>€112.40</td>
                            <td><span class="badge positive">Active</span></td>
                            <td><button class="btn btn-danger-outline btn-sm">Cancel</button></td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">Solar Farm #12</td>
                            <td><span class="badge negative">BUY</span></td>
                            <td>150</td>
                            <td>€110.00</td>
                            <td><span class="badge muted">Filled</span></td>
                            <td class="muted" style="font-size: 0.82rem;">Sep 3</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">Bucharest Hotel</td>
                            <td><span class="badge negative">BUY</span></td>
                            <td>50</td>
                            <td>€10.20</td>
                            <td><span class="badge muted">Cancelled</span></td>
                            <td class="muted" style="font-size: 0.82rem;">Aug 29</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection