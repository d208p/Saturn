@extends('layouts.app')

@section('title', 'Withdraw Funds')

@push('styles')
<style>
    .withdraw-card { max-width: 500px; margin: 0 auto; }
    .balance-box {
        background: rgba(212, 175, 55, 0.08);
        border: 1px solid var(--gold-border);
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .bank-option {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border: 1px solid var(--card-border);
        border-radius: 9px;
        background: rgba(0, 0, 0, 0.2);
        margin-bottom: 10px;
        cursor: pointer;
    }
    .bank-option input[type="radio"] { accent-color: var(--gold); }
</style>
@endpush

@section('content')
    <div style="margin-bottom: 16px; max-width: 500px; margin-left: auto; margin-right: auto;">
        <a href="{{ route('account.index') }}" class="muted" style="font-size: 0.85rem; text-decoration: none;">&larr; Back to Account</a>
    </div>

    <div class="card withdraw-card">
        <h2 style="font-size: 1.3rem; margin-bottom: 4px;">Withdraw Funds</h2>
        <p class="muted" style="font-size: 0.85rem; margin-bottom: 20px;">
            Transfer funds securely from your account balance to your verified bank account via Stripe.
        </p>

        @if($errors->has('withdrawal'))
            <div class="alert alert-error" style="margin-bottom: 16px;">
                {{ $errors->first('withdrawal') }}
            </div>
        @endif

        <!-- Balance Info -->
        <div class="balance-box">
            <div>
                <div class="muted" style="font-size: 0.78rem;">AVAILABLE CASH</div>
                <div style="font-size: 1.4rem; font-weight: 800; color: var(--gold);">
                    €{{ number_format($user->balance ?? 0, 2) }}
                </div>
            </div>
            <span class="badge positive">Ready</span>
        </div>

        @if(!$payoutsReady)
            <div class="muted" style="padding: 12px; font-size: 0.85rem; border: 1px dashed var(--card-border); border-radius: 8px; margin-bottom: 16px;">
                Your payout account isn't verified with Stripe yet.
                <a href="{{ route('account.stripe.onboarding') }}">Finish setup</a> to enable withdrawals.
            </div>
        @endif

        <form action="{{ route('withdraw.process') }}" method="POST">
            @csrf

            <!-- Select Destination Account -->
            <div class="field">
                <label>Select Destination Bank Account</label>
                @forelse($bankAccounts as $index => $bank)
                    <label class="bank-option">
                        <input type="radio" name="external_account_id" value="{{ $bank['id'] }}" {{ $bank['default_for_currency'] || $index === 0 ? 'checked' : '' }}>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem;">{{ $bank['bank_name'] }}</div>
                            <div class="muted" style="font-size: 0.78rem;">•••• {{ $bank['last4'] }} · {{ $bank['currency'] }}</div>
                        </div>
                    </label>
                @empty
                    <div class="muted" style="padding: 12px; font-size: 0.85rem; border: 1px dashed var(--card-border); border-radius: 8px;">
                        No bank account connected. Please add one in your account settings first.
                    </div>
                @endforelse
            </div>

            <!-- Amount Input -->
            <div class="field" style="margin-top: 16px;">
                <label for="amount">Withdrawal Amount (€)</label>
                <input
                    type="number"
                    id="amount"
                    name="amount"
                    step="0.01"
                    min="10"
                    max="{{ $user->balance ?? 0 }}"
                    placeholder="100.00"
                    required
                    {{ ($user->balance ?? 0) < 10 ? 'disabled' : '' }}
                >
                <div class="muted" style="font-size: 0.75rem; margin-top: 4px;">Minimum withdrawal amount: €10.00</div>
            </div>

            <!-- Processing Time Note -->
            <div style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 8px; margin: 16px 0; font-size: 0.8rem;" class="muted">
                ⚡ Payouts are processed through Stripe and typically arrive in your bank account within 1–3 business days.
            </div>

            <button
                type="submit"
                class="btn btn-gold btn-block"
                style="width: 100%; margin-top: 8px;"
                {{ (!$payoutsReady) || ($user->balance ?? 0) < 10 || count($bankAccounts) === 0 ? 'disabled' : '' }}
            >
                Confirm Withdrawal
            </button>
        </form>
    </div>
@endsection