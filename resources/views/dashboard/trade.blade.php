@extends('layouts.app')

@section('title', 'Trade - ' . $asset->title)

@push('styles')
<style>
    .trade-card { max-width: 420px; margin: 0 auto; }
    .qty-stepper { display: flex; align-items: center; gap: 12px; margin: 10px 0 20px; }
    .qty-btn {
        width: 40px; height: 40px; border-radius: 9px; border: 1px solid var(--card-border);
        background: transparent; color: var(--ivory); display: flex; align-items: center;
        justify-content: center; cursor: pointer; flex-shrink: 0;
    }
    .qty-btn:hover { border-color: var(--gold-border); color: var(--gold); }
    .qty-input {
        flex: 1; text-align: center; background: rgba(0,0,0,0.25); border: 1px solid var(--card-border);
        border-radius: 9px; color: var(--ivory); font-family: inherit; font-size: 1rem; font-weight: 700;
        padding: 10px; font-variant-numeric: tabular-nums;
    }
    .estimate-row { display: flex; justify-content: space-between; align-items: center; padding: 16px 0; border-top: 1px dashed var(--card-border); margin-top: 4px; }
</style>
@endpush

@section('content')
    <div class="card trade-card">
        @if($errors->has('trade'))
            <div class="alert alert-danger" style="margin-bottom: 16px; color: #ff6b6b;">
                {{ $errors->first('trade') }}
            </div>
        @endif

        <div class="tabs" data-panels="#trade-panels" style="margin-bottom: 20px;">
            <button class="tab-btn active" data-tab="buy">Buy</button>
            <button class="tab-btn" data-tab="sell">Sell</button>
        </div>

        <div id="trade-panels">

            <!-- Buy -->
            <div class="tab-panel" id="buy">
                <form action="{{ route('trade.store', $asset->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="side" value="buy">

                    <div style="font-weight: 700; font-size: 1.05rem; margin-bottom: 2px;">{{ $asset->title }}</div>
                    <div class="muted" style="font-size: 0.85rem; margin-bottom: 20px;">Secondary market listing</div>

                    <div class="list-row">
                        <div class="primary">Price</div>
                        <div class="value">€{{ number_format($asset->share_price, 2) }} / unit</div>
                    </div>
                    <div class="list-row">
                        <div class="primary">Available</div>
                        <div class="value">{{ number_format($asset->available_shares) }} units</div>
                    </div>

                    <div class="stat-label" style="margin-top: 20px;">Quantity</div>
                    <div class="qty-stepper" data-price="{{ $asset->share_price }}" data-max="{{ $asset->available_shares }}" data-total-target="#buy-total">
                        <button type="button" class="qty-btn qty-minus">−</button>
                        <input type="number" name="shares" class="qty-input" value="1" min="1" max="{{ $asset->available_shares }}">
                        <button type="button" class="qty-btn qty-plus">+</button>
                    </div>

                    <div class="estimate-row">
                        <span class="muted">Estimated total</span>
                        <span id="buy-total" style="font-size: 1.2rem; font-weight: 800;">€{{ number_format($asset->share_price, 2) }}</span>
                    </div>

                    <button type="submit" class="btn btn-gold btn-block" {{ $asset->available_shares < 1 ? 'disabled' : '' }}>Buy</button>
                </form>
            </div>

            <!-- Sell -->
            <div class="tab-panel" id="sell">
                <form action="{{ route('trade.store', $asset->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="side" value="sell">

                    <div style="font-weight: 700; font-size: 1.05rem; margin-bottom: 2px;">{{ $asset->title }}</div>
                    <div class="muted" style="font-size: 0.85rem; margin-bottom: 20px;">List units from your position for sale</div>

                    <div class="list-row">
                        <div class="primary">Your position</div>
                        <div class="value">{{ number_format($userShares) }} units</div>
                    </div>
                    <div class="list-row">
                        <div class="primary">Current market price</div>
                        <div class="value">€{{ number_format($asset->share_price, 2) }}</div>
                    </div>

                    <div class="stat-label" style="margin-top: 20px;">Quantity to sell</div>
                    <div class="qty-stepper" data-price="{{ $asset->share_price }}" data-max="{{ $userShares }}" data-total-target="#sell-total">
                        <button type="button" class="qty-btn qty-minus">−</button>
                        <input type="number" name="shares" class="qty-input" value="{{ min(1, $userShares) }}" min="1" max="{{ max(1, $userShares) }}" {{ $userShares < 1 ? 'disabled' : '' }}>
                        <button type="button" class="qty-btn qty-plus">+</button>
                    </div>

                    <div class="estimate-row">
                        <span class="muted">Estimated proceeds</span>
                        <span id="sell-total" style="font-size: 1.2rem; font-weight: 800;">€{{ number_format($asset->share_price, 2) }}</span>
                    </div>

                    <button type="submit" class="btn btn-gold btn-block" {{ $userShares < 1 ? 'disabled' : '' }}>List for sale</button>
                </form>
            </div>

        </div>

        <p class="muted" style="font-size: 0.76rem; text-align: center; margin-top: 18px;">
            Prices shown reflect the last matched trade, not a guaranteed execution price.
        </p>
    </div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.qty-stepper').forEach((stepper) => {
        const input = stepper.querySelector('.qty-input');
        const price = parseFloat(stepper.dataset.price);
        const max = parseInt(stepper.dataset.max, 10);
        const totalEl = document.querySelector(stepper.dataset.totalTarget);

        function clamp(value) {
            if (Number.isNaN(value) || value < 1) return max > 0 ? 1 : 0;
            return Math.min(max, value);
        }

        function update() {
            const qty = clamp(parseInt(input.value, 10));
            input.value = qty;
            if (totalEl) {
                totalEl.textContent = '€' + (qty * price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }

        stepper.querySelector('.qty-minus')?.addEventListener('click', () => {
            input.value = clamp((parseInt(input.value, 10) || 1) - 1);
            update();
        });
        stepper.querySelector('.qty-plus')?.addEventListener('click', () => {
            input.value = clamp((parseInt(input.value, 10) || 1) + 1);
            update();
        });
        input.addEventListener('input', update);
        update();
    });

    const side = new URLSearchParams(window.location.search).get('side');
    if (side === 'sell') {
        document.querySelector('.tabs[data-panels="#trade-panels"] .tab-btn[data-tab="sell"]')?.click();
    }
</script>
@endpush