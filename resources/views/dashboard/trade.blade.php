@extends('layouts.app')

@section('title', 'Invest - ' . $asset->title)

@push('styles')
<style>
    .market-grid { display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start; }
    @media (max-width: 868px) { .market-grid { grid-template-columns: 1fr; } }
    .summary-box { background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 9px; padding: 16px; margin: 16px 0; }
    .summary-row { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 8px; }
    .summary-row.total { font-weight: 700; font-size: 1rem; border-top: 1px dashed var(--card-border); padding-top: 8px; margin-top: 8px; }
    
    .progress-bar-bg { background: rgba(255,255,255,0.08); border-radius: 6px; height: 10px; overflow: hidden; margin: 12px 0; }
    .progress-bar-fill { background: var(--gold, #d4af37); height: 100%; transition: width 0.3s ease; }
    .info-list { width: 100%; border-collapse: collapse; margin-top: 12px; }
    .info-list td { padding: 12px 0; border-bottom: 1px solid var(--card-border); font-size: 0.88rem; }
    .info-list td:last-child { text-align: right; font-weight: 600; }
</style>
@endpush

@section('content')
    <div style="margin-bottom: 16px;">
        <a href="{{ route('market') }}" class="muted" style="font-size: 0.85rem; text-decoration: none;">&larr; Back to Market</a>
    </div>

    <div class="market-grid">
        <!-- Buy Form Card -->
        <div class="card">
            <h2 style="font-size: 1.2rem; margin-bottom: 4px;">Invest in {{ $asset->title }}</h2>
            <p class="muted" style="font-size: 0.82rem; margin-bottom: 16px;">
                Direct primary offering purchase from asset issuer.
            </p>

            @if($errors->has('trade'))
                <div class="alert alert-danger" style="margin-bottom: 16px; color: #ff6b6b; font-size: 0.85rem;">
                    {{ $errors->first('trade') }}
                </div>
            @endif

            <form action="{{ route('trade.store', $asset->id) }}" method="POST">
                @csrf

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="stat-label" style="display: block; margin-bottom: 6px;">Quantity of Shares</label>
                    <input type="number" id="shares-input" name="shares" value="1" min="1" max="{{ $asset->available_shares }}"
                           style="width: 100%; padding: 10px; background: rgba(0,0,0,0.25); border: 1px solid var(--card-border); border-radius: 8px; color: #fff; font-weight: 700;" 
                           {{ $asset->available_shares < 1 ? 'disabled' : '' }} required>
                    <span class="muted" style="font-size: 0.78rem; margin-top: 4px; display: block;">Available remaining: {{ number_format($asset->available_shares) }} units</span>
                </div>

                <div class="summary-box">
                    <div class="summary-row">
                        <span class="muted">Share Price</span>
                        <span>€{{ number_format($asset->share_price, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="muted">Subtotal</span>
                        <span id="subtotal-val">€0.00</span>
                    </div>
                    <div class="summary-row">
                        <span class="muted">Platform Fee (4.9%)</span>
                        <span id="fee-val">€0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Cost</span>
                        <span id="total-val" style="color: var(--gold, #d4af37);">€0.00</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-gold btn-block" {{ $asset->available_shares < 1 ? 'disabled' : '' }}>
                    {{ $asset->available_shares < 1 ? 'Fully Funded' : 'Confirm Investment' }}
                </button>
            </form>
        </div>

        <!-- Primary Offering Overview Card -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 1.1rem;">Offering Details</h3>
                <span class="badge positive" style="font-size: 0.75rem; text-transform: uppercase;">
                    {{ $asset->status }}
                </span>
            </div>

            @php
                $issuedShares = $asset->total_shares - $asset->available_shares;
                $percentFunded = $asset->total_shares > 0 ? min(100, round(($issuedShares / $asset->total_shares) * 100, 1)) : 0;
            @endphp

            <div style="margin: 16px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                    <span class="muted">Funding Progress</span>
                    <span style="font-weight: 700; color: var(--gold, #d4af37);">{{ $percentFunded }}%</span>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: {{ $percentFunded }}%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.78rem;" class="muted">
                    <span>{{ number_format($issuedShares) }} shares sold</span>
                    <span>{{ number_format($asset->total_shares) }} total shares</span>
                </div>
            </div>

            <table class="info-list">
                <tbody>
                    <tr>
                        <td class="muted">Category</td>
                        <td>{{ $asset->category }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Total Valuation</td>
                        <td>€{{ number_format($asset->total_valuation, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Price Per Share</td>
                        <td style="color: var(--gold, #d4af37);">€{{ number_format($asset->share_price, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="muted">Available Units</td>
                        <td>{{ number_format($asset->available_shares) }} units</td>
                    </tr>
                </tbody>
            </table>

            @if($asset->description)
                <div style="margin-top: 20px;">
                    <div class="stat-label" style="margin-bottom: 6px;">About this Opportunity</div>
                    <p class="muted" style="font-size: 0.85rem; line-height: 1.5;">
                        {{ $asset->description }}
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const sharePrice = {{ (float) $asset->share_price }};
    const feePercent = {{ \App\Http\Controllers\TradeController::PLATFORM_FEE_PERCENT }};
    
    const sharesInput = document.getElementById('shares-input');
    const subtotalEl = document.getElementById('subtotal-val');
    const feeEl = document.getElementById('fee-val');
    const totalEl = document.getElementById('total-val');

    function calculateTotal() {
        let shares = parseInt(sharesInput.value, 10) || 0;
        if (shares < 0) shares = 0;

        let subtotal = shares * sharePrice;
        let fee = subtotal * feePercent;
        let total = subtotal + fee;

        subtotalEl.textContent = '€' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        feeEl.textContent = '€' + fee.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        totalEl.textContent = '€' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (sharesInput) {
        sharesInput.addEventListener('input', calculateTotal);
        calculateTotal();
    }
</script>
@endpush