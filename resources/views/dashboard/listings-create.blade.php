@extends('layouts.app')

@section('title', 'List Asset for Sale - ' . $asset->title)

@push('styles')
<style>
    .listing-create-card { max-width: 480px; margin: 0 auto; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: var(--ivory); }
    .form-control {
        width: 100%; background: rgba(0,0,0,0.25); border: 1px solid var(--card-border);
        border-radius: 9px; color: var(--ivory); font-family: inherit; font-size: 1rem;
        font-weight: 600; padding: 12px; font-variant-numeric: tabular-nums; box-sizing: border-box;
    }
    .form-control:focus { outline: none; border-color: var(--gold-border); }
    .input-hint { font-size: 0.78rem; color: #8a8a8a; margin-top: 4px; display: block; }
    .summary-box {
        background: rgba(255,255,255,0.03); border: 1px solid var(--card-border);
        border-radius: 9px; padding: 16px; margin: 24px 0;
    }
    .summary-row { display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 8px; }
    .summary-row:last-child { margin-bottom: 0; padding-top: 8px; border-top: 1px dashed var(--card-border); font-weight: 700; }
</style>
@endpush

@section('content')
    <div class="card listing-create-card">
        <div style="margin-bottom: 18px;">
            <a href="{{ route('market') }}" class="muted" style="font-size: 0.85rem; text-decoration: none;">&larr; Back to Market</a>
        </div>

        <h2 style="font-size: 1.3rem; margin-bottom: 4px;">List Shares for Sale</h2>
        <p class="muted" style="font-size: 0.85rem; margin-bottom: 24px;">
            Set your desired price and quantity to list on the secondary market.
        </p>

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 20px; color: #ff6b6b; font-size: 0.88rem;">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('listings.store', $asset->id) }}" method="POST">
            @csrf

            <!-- Asset Info -->
            <div class="list-row" style="margin-bottom: 12px;">
                <div class="primary">Asset Name</div>
                <div class="value" style="font-weight: 700;">{{ $asset->title }}</div>
            </div>
            <div class="list-row" style="margin-bottom: 12px;">
                <div class="primary">Your Total Position</div>
                <div class="value">{{ number_format($userShares) }} units</div>
            </div>
            <div class="list-row" style="margin-bottom: 20px;">
                <div class="primary">Primary Share Price</div>
                <div class="value">€{{ number_format($asset->share_price, 2) }}</div>
            </div>

            <!-- Quantity Input -->
            <div class="form-group">
                <label class="form-label" for="shares">Quantity to List</label>
                <input type="number" id="shares" name="shares" class="form-control" 
                       value="{{ old('shares', 1) }}" min="1" max="{{ $userShares }}" required>
                <span class="input-hint">Maximum available: {{ number_format($userShares) }} units</span>
            </div>

            <!-- Custom Price Input -->
            <div class="form-group">
                <label class="form-label" for="price_per_share">Ask Price per Share (€)</label>
                <input type="number" step="0.01" id="price_per_share" name="price_per_share" class="form-control" 
                       value="{{ old('price_per_share', $asset->share_price) }}" min="0.01" required>
                <span class="input-hint">You can set any price per unit for the secondary market.</span>
            </div>

            <!-- Summary Calculation -->
            <div class="summary-box">
                <div class="summary-row">
                    <span class="muted">Units to sell</span>
                    <span id="summary-units">1</span>
                </div>
                <div class="summary-row">
                    <span class="muted">Price per unit</span>
                    <span id="summary-price">€{{ number_format($asset->share_price, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Total Listing Value</span>
                    <span id="summary-total" style="color: var(--gold);">€{{ number_format($asset->share_price, 2) }}</span>
                </div>
            </div>

            <button type="submit" class="btn btn-gold btn-block">Publish Listing</button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const sharesInput = document.getElementById('shares');
    const priceInput = document.getElementById('price_per_share');
    const summaryUnits = document.getElementById('summary-units');
    const summaryPrice = document.getElementById('summary-price');
    const summaryTotal = document.getElementById('summary-total');

    function updateSummary() {
        const units = parseInt(sharesInput.value, 10) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = units * price;

        summaryUnits.textContent = units.toLocaleString();
        summaryPrice.textContent = '€' + price.toFixed(2);
        summaryTotal.textContent = '€' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    sharesInput.addEventListener('input', updateSummary);
    priceInput.addEventListener('input', updateSummary);
    updateSummary();
</script>
@endpush