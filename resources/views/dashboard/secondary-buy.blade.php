@extends('layouts.app')

@section('title', 'Buy Secondary Listing - ' . $sellOrder->asset->title)

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
        <div style="margin-bottom: 16px;">
            <a href="{{ route('market') }}" class="muted" style="font-size: 0.85rem; text-decoration: none;">&larr; Back to Market</a>
        </div>

        @if($errors->has('buy'))
            <div class="alert alert-danger" style="margin-bottom: 16px; color: #ff6b6b;">
                {{ $errors->first('buy') }}
            </div>
        @endif

        <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 2px;">{{ $sellOrder->asset->title }}</div>
        <div class="muted" style="font-size: 0.85rem; margin-bottom: 20px;">
            Secondary Order by {{ $sellOrder->user->name ?? 'User #'.$sellOrder->user_id }}
        </div>

        <form action="{{ route('secondary.buy.process', $sellOrder->id) }}" method="POST">
            @csrf

            <div class="list-row">
                <div class="primary">Ask Price</div>
                <div class="value">€{{ number_format($sellOrder->price_per_share, 2) }} / share</div>
            </div>
            <div class="list-row">
                <div class="primary">Available Units</div>
                <div class="value">{{ number_format($sellOrder->shares) }} units</div>
            </div>
            <div class="list-row">
                <div class="primary">Your Balance</div>
                <div class="value">€{{ number_format(Auth::user()->balance, 2) }}</div>
            </div>

            <div class="stat-label" style="margin-top: 20px;">Quantity to Buy</div>
            <div class="qty-stepper" data-price="{{ $sellOrder->price_per_share }}" data-max="{{ $sellOrder->shares }}" data-total-target="#buy-total">
                <button type="button" class="qty-btn qty-minus">−</button>
                <input type="number" name="shares" class="qty-input" value="1" min="1" max="{{ $sellOrder->shares }}">
                <button type="button" class="qty-btn qty-plus">+</button>
            </div>

            <div class="estimate-row">
                <span class="muted">Total Cost</span>
                <span id="buy-total" style="font-size: 1.2rem; font-weight: 800;">€{{ number_format($sellOrder->price_per_share, 2) }}</span>
            </div>

            <button type="submit" class="btn btn-gold btn-block">Confirm Purchase</button>
        </form>
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
</script>
@endpush