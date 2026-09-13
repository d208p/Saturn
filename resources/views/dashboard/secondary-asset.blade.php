@extends('layouts.app')

@section('title', 'Market Listings - ' . $asset->title)

@push('styles')
<style>
    .market-grid { display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start; }
    @media (max-width: 868px) { .market-grid { grid-template-columns: 1fr; } }
    .order-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    .order-table th, .order-table td { padding: 10px; text-align: left; border-bottom: 1px solid var(--card-border); font-size: 0.88rem; }
    .summary-box { background: rgba(0,0,0,0.2); border: 1px solid var(--card-border); border-radius: 9px; padding: 16px; margin: 16px 0; }
    .summary-row { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 8px; }
    .summary-row.total { font-weight: 700; font-size: 1rem; border-top: 1px dashed var(--card-border); padding-top: 8px; margin-top: 8px; }
</style>
@endpush

@section('content')
    <div style="margin-bottom: 16px;">
        <a href="{{ route('market') }}" class="muted" style="font-size: 0.85rem; text-decoration: none;">&larr; Back to Market</a>
    </div>

    <div class="market-grid">
        <!-- Buy Form Card -->
        <div class="card">
            <h2 style="font-size: 1.2rem; margin-bottom: 4px;">Buy Shares</h2>
            <p class="muted" style="font-size: 0.82rem; margin-bottom: 16px;">
                Cheapest available listings are automatically matched first.
            </p>

            @if($errors->has('buy'))
                <div class="alert alert-danger" style="margin-bottom: 16px; color: #ff6b6b; font-size: 0.85rem;">
                    {{ $errors->first('buy') }}
                </div>
            @endif

            <form action="{{ route('secondary.asset.buy', $asset->id) }}" method="POST">
                @csrf

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="stat-label" style="display: block; margin-bottom: 6px;">Quantity to Buy</label>
                    <input type="number" id="shares-input" name="shares" value="1" min="1" max="{{ $totalAvailableShares }}"
                           style="width: 100%; padding: 10px; background: rgba(0,0,0,0.25); border: 1px solid var(--card-border); border-radius: 8px; color: #fff; font-weight: 700;" required>
                    <span class="muted" style="font-size: 0.78rem; margin-top: 4px; display: block;">Max available: {{ number_format($totalAvailableShares) }} units</span>
                </div>

                <div class="summary-box">
                    <div class="summary-row">
                        <span class="muted">Subtotal</span>
                        <span id="subtotal-val">€0.00</span>
                    </div>
                    <div class="summary-row">
                        <span class="muted">Platform Fee (3.3%)</span>
                        <span id="fee-val">€0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total Cost</span>
                        <span id="total-val" style="color: var(--gold);">€0.00</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-gold btn-block">Buy Now</button>
            </form>
        </div>

        <!-- Order Book / Listings Table -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 1.1rem;">Market Listings for {{ $asset->title }}</h3>
                <span class="muted" style="font-size: 0.82rem;">Sorted by lowest price</span>
            </div>

            <table class="order-table">
                <thead>
                    <tr>
                        <th>Seller</th>
                        <th>Price / Unit</th>
                        <th>Available Units</th>
                        <th>Subtotal Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listings as $index => $listing)
                        <tr style="{{ $index === 0 ? 'background: rgba(212, 175, 55, 0.05);' : '' }}">
                            <td>
                                {{ $listing->user->name ?? 'User #'.$listing->user_id }}
                                @if($index === 0)
                                    <span class="badge positive" style="font-size: 0.68rem; margin-left: 4px;">Cheapest</span>
                                @endif
                            </td>
                            <td style="font-weight: 700; color: var(--gold);">€{{ number_format($listing->price_per_share, 2) }}</td>
                            <td>{{ number_format($listing->shares) }}</td>
                            <td class="muted">€{{ number_format($listing->shares * $listing->price_per_share, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="muted" style="text-align: center; padding: 20px;">No listings currently available for this asset.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // JS ladder calculation for instantaneous dynamic price feedback
    const listings = @json($listings->map(fn($l) => ['shares' => $l->shares, 'price' => (float)$l->price_per_share]));
    const sharesInput = document.getElementById('shares-input');
    const subtotalEl = document.getElementById('subtotal-val');
    const feeEl = document.getElementById('fee-val');
    const totalEl = document.getElementById('total-val');

    function calculateTotal() {
        let req = parseInt(sharesInput.value, 10) || 0;
        let subtotal = 0;

        for (let item of listings) {
            if (req <= 0) break;
            let take = Math.min(req, item.shares);
            subtotal += take * item.price;
            req -= take;
        }

        let fee = subtotal * 0.033;
        let total = subtotal + fee;

        subtotalEl.textContent = '€' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        feeEl.textContent = '€' + fee.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        totalEl.textContent = '€' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    sharesInput.addEventListener('input', calculateTotal);
    calculateTotal();
</script>
@endpush