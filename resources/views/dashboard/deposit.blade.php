@extends('layouts.app')

@section('title', 'Deposit Funds')

@push('styles')
<style>
    .deposit-card {
        max-width: 460px;
        margin: 0 auto;
    }

    .amount-presets {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin: 12px 0 20px;
    }

    .preset-btn {
        padding: 10px;
        border-radius: 8px;
        border: 1px solid var(--card-border);
        background: rgba(0, 0, 0, 0.2);
        color: var(--ivory);
        font-weight: 700;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
    }

    .preset-btn:hover,
    .preset-btn.active {
        border-color: var(--gold-border);
        color: var(--gold);
        background: rgba(212, 175, 55, 0.08);
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .currency-symbol {
        position: absolute;
        left: 14px;
        font-weight: 700;
        color: var(--gold);
        font-size: 1.1rem;
    }

    .deposit-input {
        width: 100%;
        padding: 12px 14px 12px 34px;
        background: rgba(0, 0, 0, 0.25);
        border: 1px solid var(--card-border);
        border-radius: 9px;
        color: #fff;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .deposit-input:focus {
        outline: none;
        border-color: var(--gold-border);
    }

    #card-element {
        padding: 14px;
        background: rgba(0, 0, 0, 0.25);
        border: 1px solid var(--card-border);
        border-radius: 9px;
        margin-bottom: 20px;
    }

    .summary-box {
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid var(--card-border);
        border-radius: 9px;
        padding: 16px;
        margin: 16px 0;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.88rem;
        margin-bottom: 8px;
    }

    .summary-row.total {
        font-weight: 700;
        font-size: 1.05rem;
        border-top: 1px dashed var(--card-border);
        padding-top: 10px;
        margin-top: 10px;
    }

    #payment-success {
        display: none;
        margin-bottom: 16px;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid rgba(74, 222, 128, 0.3);
        background: rgba(74, 222, 128, 0.08);
        color: #4ade80;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')

    <div style="margin-bottom: 16px; max-width: 460px; margin-left: auto; margin-right: auto;">
        <a href="{{ route('portfolio') }}"
           class="muted"
           style="font-size: 0.85rem; text-decoration: none;">
            &larr; Back to Portfolio
        </a>
    </div>

    <div class="card deposit-card">

        <h2 style="font-size: 1.3rem; margin-bottom: 4px;">
            Deposit Funds
        </h2>

        <p class="muted" style="font-size: 0.85rem; margin-bottom: 20px;">
            Add funds using your credit or debit card.
        </p>

        @if($errors->has('deposit'))
            <div class="alert alert-danger"
                 style="margin-bottom: 16px; color: #ff6b6b; font-size: 0.85rem;">
                {{ $errors->first('deposit') }}
            </div>
        @endif

        <div id="payment-error"
             style="display: none; margin-bottom: 16px; color: #ff6b6b; font-size: 0.85rem;">
        </div>

        <div id="payment-success">
            Payment successful. Your Saturn balance will update shortly.
        </div>

        <!-- Amount -->

        <div class="stat-label" style="margin-bottom: 6px;">
            Select Amount
        </div>

        <div class="amount-presets">

            <button type="button"
                    class="preset-btn"
                    data-amount="50">
                €50
            </button>

            <button type="button"
                    class="preset-btn active"
                    data-amount="100">
                €100
            </button>

            <button type="button"
                    class="preset-btn"
                    data-amount="250">
                €250
            </button>

            <button type="button"
                    class="preset-btn"
                    data-amount="500">
                €500
            </button>

        </div>

        <div class="stat-label" style="margin-bottom: 6px;">
            Custom Amount
        </div>

        <div class="input-wrapper">

            <span class="currency-symbol">
                €
            </span>

            <input
                type="number"
                id="deposit-amount"
                value="100.00"
                min="5"
                max="10000"
                step="5"
                class="deposit-input"
            >

        </div>

        <!-- Card -->

        <div class="stat-label" style="margin-bottom: 6px;">
            Card Details
        </div>

        <div id="card-element"></div>

        <!-- Summary -->

        <div class="summary-box">

            <div class="summary-row">
                <span class="muted">
                    Deposit Amount
                </span>

                <span id="summary-subtotal">
                    €100.00
                </span>
            </div>

            <div class="summary-row">

                <span class="muted">
                    Processing Fee
                </span>

                <span style="color: #4ade80;">
                    Free
                </span>

            </div>

            <div class="summary-row total">

                <span>
                    Account Credit
                </span>

                <span id="summary-total"
                      style="color: var(--gold);">
                    €100.00
                </span>

            </div>

        </div>

        <button
            type="button"
            id="pay-button"
            class="btn btn-gold btn-block">
            Pay Now
        </button>

    </div>

@endsection

@push('scripts')

<script src="https://js.stripe.com/v3/"></script>

<script>

    const stripe = Stripe(@json($stripeKey));

    const elements = stripe.elements();

    const cardElement = elements.create('card', {
        style: {
            base: {
                color: '#ffffff',
                fontFamily: 'inherit',
                fontSmoothing: 'antialiased',
                fontSize: '15px',

                '::placeholder': {
                    color: '#a0aec0'
                }
            },

            invalid: {
                color: '#ff6b6b',
                iconColor: '#ff6b6b'
            }
        }
    });

    cardElement.mount('#card-element');


    const amountInput =
        document.getElementById('deposit-amount');

    const subtotalEl =
        document.getElementById('summary-subtotal');

    const totalEl =
        document.getElementById('summary-total');

    const presetBtns =
        document.querySelectorAll('.preset-btn');

    const payBtn =
        document.getElementById('pay-button');

    const errorBox =
        document.getElementById('payment-error');

    const successBox =
        document.getElementById('payment-success');


    function updateSummary(value) {

        const amount =
            parseFloat(value) || 0;

        const formatted =
            '€' +
            amount.toLocaleString(
                'en-US',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );

        subtotalEl.textContent = formatted;

        totalEl.textContent = formatted;
    }


    presetBtns.forEach(button => {

        button.addEventListener('click', () => {

            presetBtns.forEach(btn => {
                btn.classList.remove('active');
            });

            button.classList.add('active');

            amountInput.value =
                parseFloat(
                    button.dataset.amount
                ).toFixed(2);

            updateSummary(
                amountInput.value
            );
        });

    });


    amountInput.addEventListener(
        'input',
        event => {

            presetBtns.forEach(btn => {
                btn.classList.remove('active');
            });

            updateSummary(
                event.target.value
            );
        }
    );


    function showError(message) {

        errorBox.textContent = message;
        errorBox.style.display = 'block';

        successBox.style.display = 'none';
    }


    function resetButton() {

        payBtn.disabled = false;
        payBtn.textContent = 'Pay Now';
    }


    payBtn.addEventListener(
        'click',
        async () => {

            payBtn.disabled = true;
            payBtn.textContent = 'Processing...';

            errorBox.style.display = 'none';
            successBox.style.display = 'none';


            const amount =
                parseFloat(
                    amountInput.value
                );


            if (
                Number.isNaN(amount) ||
                amount < 5 ||
                amount > 10000
            ) {

                showError(
                    'Deposit amount must be between €5.00 and €10,000.00.'
                );

                resetButton();

                return;
            }


            try {

                /*
                 * 1. Ask Laravel to create
                 *    the Stripe PaymentIntent.
                 */

                const response =
                    await fetch(
                        "{{ route('deposit.createPaymentIntent') }}",
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type': 'application/json',

                                'Accept': 'application/json',

                                'X-CSRF-TOKEN':
                                    "{{ csrf_token() }}"
                            },

                            body: JSON.stringify({
                                amount: amount
                            })
                        }
                    );


                const data =
                    await response.json();


                if (!response.ok) {

                    throw new Error(
                        data.error ||
                        'Failed to initialize payment.'
                    );
                }


                if (!data.clientSecret) {

                    throw new Error(
                        'Stripe did not return a client secret.'
                    );
                }


                /*
                 * 2. Confirm the payment with Stripe.
                 */

                const result =
                    await stripe.confirmCardPayment(
                        data.clientSecret,
                        {
                            payment_method: {
                                card: cardElement
                            }
                        }
                    );


                /*
                 * Stripe.js detected an error.
                 */

                if (result.error) {

                    showError(
                        result.error.message ||
                        'Payment failed.'
                    );

                    resetButton();

                    return;
                }


                /*
                 * 3. Payment succeeded.
                 *
                 * DO NOT update the Saturn balance here.
                 *
                 * Stripe's webhook will do that.
                 */

                if (
                    result.paymentIntent &&
                    result.paymentIntent.status === 'succeeded'
                ) {

                    payBtn.textContent =
                        'Payment Successful';

                    successBox.style.display =
                        'block';

                    /*
                     * Give the webhook a moment to
                     * reach Laravel, then reload.
                     *
                     * The webhook is still the
                     * authoritative balance update.
                     */

                    setTimeout(() => {
                        window.location.reload();
                    }, 2500);

                    return;
                }


                /*
                 * Payment may require additional
                 * action or have another status.
                 */

                if (
                    result.paymentIntent &&
                    result.paymentIntent.status === 'processing'
                ) {

                    successBox.textContent =
                        'Payment is processing. Your balance will update once Stripe confirms the payment.';

                    successBox.style.display =
                        'block';

                    resetButton();

                    return;
                }


                throw new Error(
                    'Payment was not completed.'
                );

            } catch (error) {

                console.error(
                    'Deposit error:',
                    error
                );

                showError(
                    error.message ||
                    'Unable to process payment.'
                );

                resetButton();
            }

        }
    );

</script>

@endpush