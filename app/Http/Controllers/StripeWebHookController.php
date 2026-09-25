<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;

class StripeWebhookController extends Controller
{
    /**
     * Receive Stripe webhook events.
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();

        $signature = $request->header(
            'Stripe-Signature'
        );

        $webhookSecret = config(
            'services.stripe.webhook_secret'
        );


        /*
        |--------------------------------------------------------------------------
        | Verify Stripe webhook
        |--------------------------------------------------------------------------
        */

        try {

            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );

        } catch (\UnexpectedValueException $e) {

            Log::warning(
                'Invalid Stripe webhook payload.'
            );

            return response(
                'Invalid payload',
                400
            );

        } catch (SignatureVerificationException $e) {

            Log::warning(
                'Invalid Stripe webhook signature.'
            );

            return response(
                'Invalid signature',
                400
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Process event
        |--------------------------------------------------------------------------
        */

        try {

            switch ($event->type) {

                /*
                |--------------------------------------------------------------------------
                | DEPOSITS
                |--------------------------------------------------------------------------
                */

                case 'payment_intent.succeeded':

                    $this->handleSuccessfulPayment(
                        $event->data->object
                    );

                    break;


                case 'payment_intent.payment_failed':

                    $this->handleFailedPayment(
                        $event->data->object
                    );

                    break;


                /*
                |--------------------------------------------------------------------------
                | WITHDRAWALS
                |--------------------------------------------------------------------------
                */

                case 'payout.paid':

                    $this->handlePayoutPaid(
                        $event->data->object
                    );

                    break;


                case 'payout.failed':

                    $this->handlePayoutFailed(
                        $event->data->object
                    );

                    break;


                case 'payout.canceled':

                    $this->handlePayoutCanceled(
                        $event->data->object
                    );

                    break;
            }


            /*
             * Stripe received a successful response.
             */
            return response(
                'Webhook received',
                200
            );


        } catch (Throwable $e) {

            Log::error(
                'Stripe webhook processing failed.',
                [
                    'event_id' =>
                        $event->id,

                    'event_type' =>
                        $event->type,

                    'error' =>
                        $e->getMessage(),
                ]
            );


            /*
             * Returning 500 tells Stripe that processing failed.
             *
             * Stripe can then retry the webhook.
             */
            return response(
                'Webhook processing failed',
                500
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DEPOSIT
    |--------------------------------------------------------------------------
    */

    /**
     * Credit Saturn balance after Stripe confirms payment.
     */
    private function handleSuccessfulPayment(
        $paymentIntent
    ) {
        $reference =
            $paymentIntent->id;


        $userId =
            $paymentIntent->metadata->user_id
            ?? null;


        if (!$userId) {

            throw new \Exception(
                'PaymentIntent has no user_id metadata.'
            );
        }


        /*
         * Stripe amounts are stored in cents.
         *
         * Example:
         *
         * €100.00
         * becomes
         * 10000 cents
         */
        $amountInCents =
            (int) $paymentIntent->amount;


        /*
         * Saturn currently stores balance
         * as a decimal EUR value.
         */
        $amount =
            $amountInCents / 100;


        DB::transaction(function () use (
            $userId,
            $amount,
            $reference,
            $paymentIntent
        ) {

            /*
             |--------------------------------------------------------------------------
             | Idempotency protection
             |--------------------------------------------------------------------------
             |
             | Stripe may send the same webhook more than once.
             |
             | If this PaymentIntent was already processed,
             | DO NOT credit the user again.
             |
             */

            $existingTransaction =
                Transaction::where(
                    'reference',
                    $reference
                )
                ->lockForUpdate()
                ->first();


            if ($existingTransaction) {

                Log::info(
                    'Duplicate Stripe deposit webhook ignored.',
                    [
                        'payment_intent' =>
                            $reference,
                    ]
                );

                return;
            }


            /*
             |--------------------------------------------------------------------------
             | Lock user
             |--------------------------------------------------------------------------
             */

            $user =
                DB::table('users')
                    ->where(
                        'id',
                        $userId
                    )
                    ->lockForUpdate()
                    ->first();


            if (!$user) {

                throw new \Exception(
                    'User not found: ' .
                    $userId
                );
            }


            /*
             |--------------------------------------------------------------------------
             | Credit Saturn balance
             |--------------------------------------------------------------------------
             */

            DB::table('users')
                ->where(
                    'id',
                    $userId
                )
                ->increment(
                    'balance',
                    $amount
                );


            /*
             |--------------------------------------------------------------------------
             | Record transaction
             |--------------------------------------------------------------------------
             */

            Transaction::create([

                'user_id' =>
                    $userId,

                'type' =>
                    'deposit',

                'amount' =>
                    $amount,

                'status' =>
                    'completed',

                'reference' =>
                    $reference,

                'description' =>
                    'EUR card deposit via Stripe.',
            ]);


            Log::info(
                'Saturn deposit completed.',
                [
                    'payment_intent' =>
                        $reference,

                    'user_id' =>
                        $userId,

                    'amount' =>
                        $amount,
                ]
            );
        });
    }


    /**
     * Payment failed.
     */
    private function handleFailedPayment(
        $paymentIntent
    ) {

        Log::warning(
            'Stripe payment failed.',
            [
                'payment_intent' =>
                    $paymentIntent->id,

                'user_id' =>
                    $paymentIntent
                        ->metadata
                        ->user_id
                        ?? null,

                'amount' =>
                    isset(
                        $paymentIntent->amount
                    )
                        ? $paymentIntent->amount / 100
                        : null,

                'last_payment_error' =>
                    isset(
                        $paymentIntent
                            ->last_payment_error
                            ->message
                    )
                        ? $paymentIntent
                            ->last_payment_error
                            ->message
                        : null,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | WITHDRAWAL — PAID
    |--------------------------------------------------------------------------
    */

    /**
     * Stripe successfully paid the user's bank account.
     */
    private function handlePayoutPaid(
        $payout
    ) {

        $transactionId =
            $payout->metadata->transaction_id
            ?? null;


        /*
         * We attach transaction_id to the Stripe
         * payout when creating the withdrawal.
         */
        if (!$transactionId) {

            Log::warning(
                'Stripe payout has no transaction_id metadata.',
                [
                    'payout_id' =>
                        $payout->id,
                ]
            );

            return;
        }


        DB::transaction(function () use (
            $transactionId,
            $payout
        ) {

            /*
             * Lock the withdrawal transaction.
             */
            $transaction =
                Transaction::where(
                    'id',
                    $transactionId
                )
                ->lockForUpdate()
                ->first();


            if (!$transaction) {

                throw new \Exception(
                    'Withdrawal transaction not found: ' .
                    $transactionId
                );
            }


            /*
             * If already completed, ignore duplicate
             * webhook.
             */
            if (
                $transaction->status === 'completed'
            ) {

                return;
            }


            /*
             * Mark withdrawal completed.
             */
            $transaction->update([

                'status' =>
                    'completed',

                'stripe_payout_id' =>
                    $payout->id,
            ]);


            Log::info(
                'Saturn withdrawal completed.',
                [
                    'transaction_id' =>
                        $transaction->id,

                    'payout_id' =>
                        $payout->id,

                    'user_id' =>
                        $transaction->user_id,

                    'amount' =>
                        $transaction->amount,
                ]
            );
        });
    }


    /*
    |--------------------------------------------------------------------------
    | WITHDRAWAL — FAILED
    |--------------------------------------------------------------------------
    */

    /**
     * Stripe failed to send the payout.
     *
     * The money was already removed from Saturn's
     * internal balance when the withdrawal was created.
     *
     * Therefore we restore it here.
     */
    private function handlePayoutFailed(
        $payout
    ) {

        $transactionId =
            $payout->metadata->transaction_id
            ?? null;


        if (!$transactionId) {

            Log::warning(
                'Failed Stripe payout has no transaction_id metadata.',
                [
                    'payout_id' =>
                        $payout->id,
                ]
            );

            return;
        }


        DB::transaction(function () use (
            $transactionId,
            $payout
        ) {

            /*
             * Lock withdrawal.
             */
            $transaction =
                Transaction::where(
                    'id',
                    $transactionId
                )
                ->lockForUpdate()
                ->first();


            if (!$transaction) {

                throw new \Exception(
                    'Withdrawal transaction not found: ' .
                    $transactionId
                );
            }


            /*
             * If this withdrawal has already been
             * completed or failed, do nothing.
             */
            if (
                in_array(
                    $transaction->status,
                    [
                        'completed',
                        'failed',
                    ],
                    true
                )
            ) {

                return;
            }


            /*
             |--------------------------------------------------------------------------
             | Lock user
             |--------------------------------------------------------------------------
             */

            $user =
                DB::table('users')
                    ->where(
                        'id',
                        $transaction->user_id
                    )
                    ->lockForUpdate()
                    ->first();


            if (!$user) {

                throw new \Exception(
                    'User not found: ' .
                    $transaction->user_id
                );
            }


            /*
             |--------------------------------------------------------------------------
             | Restore Saturn balance
             |--------------------------------------------------------------------------
             */

            DB::table('users')
                ->where(
                    'id',
                    $transaction->user_id
                )
                ->increment(
                    'balance',
                    $transaction->amount
                );


            /*
             |--------------------------------------------------------------------------
             | Mark withdrawal failed
             |--------------------------------------------------------------------------
             */

            $transaction->update([

                'status' =>
                    'failed',

                'stripe_payout_id' =>
                    $payout->id,
            ]);


            Log::warning(
                'Saturn withdrawal failed. Balance restored.',
                [
                    'transaction_id' =>
                        $transaction->id,

                    'payout_id' =>
                        $payout->id,

                    'user_id' =>
                        $transaction->user_id,

                    'amount_restored' =>
                        $transaction->amount,
                ]
            );
        });
    }


    /*
    |--------------------------------------------------------------------------
    | WITHDRAWAL — CANCELED
    |--------------------------------------------------------------------------
    */

    /**
     * Stripe canceled the payout.
     *
     * Since the user did not receive the money,
     * restore the Saturn balance.
     */
    private function handlePayoutCanceled(
        $payout
    ) {

        /*
         * Treat cancellation exactly like
         * a failed payout.
         */
        $this->handlePayoutFailed(
            $payout
        );
    }
}