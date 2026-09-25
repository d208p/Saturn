<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Throwable;

class DepositController extends Controller
{
    /**
     * Display deposit page.
     */
    public function show()
    {
        return view('dashboard.deposit', [
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    /**
     * Create a Stripe PaymentIntent.
     *
     * IMPORTANT:
     * This only creates the payment.
     *
     * Saturn's balance is NOT increased here.
     * The Stripe webhook does that after Stripe confirms
     * that the payment actually succeeded.
     */
    public function createPaymentIntent(Request $request)
    {
        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:5',
                'max:10000',
            ],
        ]);

        $user = Auth::user();

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $amount = round((float) $validated['amount'], 2);
            $amountInCents = (int) round($amount * 100);

            $paymentIntent = PaymentIntent::create(
                [
                    'amount' => $amountInCents,
                    'currency' => 'eur',

                    'payment_method_types' => [
                        'card',
                    ],

                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'deposit_amount' => number_format(
                            $amount,
                            2,
                            '.',
                            ''
                        ),
                    ],
                ],
                [
                    'idempotency_key' =>
                        'deposit_' .
                        $user->id .
                        '_' .
                        Str::uuid(),
                ]
            );

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'error' => 'Unable to create payment.',
            ], 500);
        }
    }
}