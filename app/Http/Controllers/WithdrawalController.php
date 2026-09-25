<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Throwable;

class WithdrawalController extends Controller
{
    /**
     * Display withdrawal page.
     *
     * Stripe is used as the source of truth for:
     * - Connected account readiness
     * - Linked external bank accounts
     */
    public function show()
    {
        $user = Auth::user();

        $bankAccounts = [];
        $payoutsReady = false;

        if ($user->stripe_account_id) {
            try {
                $stripe = new StripeClient(config('services.stripe.secret'));

                $account = $stripe->v2->core->accounts->retrieve(
                    $user->stripe_account_id,
                    [
                        'include' => ['configuration.recipient'],
                    ]
                );

                $status =
                    $account->configuration->recipient->capabilities
                        ->stripe_balance->stripe_transfers->status
                    ?? null;

                $payoutsReady = $status === 'active';

                if ($payoutsReady) {
                    $externalAccounts = $stripe->accounts->allExternalAccounts(
                        $user->stripe_account_id,
                        [
                            'object' => 'bank_account',
                            'limit' => 10,
                        ]
                    );

                    foreach ($externalAccounts->data as $ext) {
                        $bankAccounts[] = [
                            'id' => $ext->id,
                            'bank_name' => $ext->bank_name ?: 'Bank account',
                            'last4' => $ext->last4,
                            'currency' => strtoupper($ext->currency),
                            'default_for_currency' => (bool) $ext->default_for_currency,
                        ];
                    }
                }
            } catch (Throwable $e) {
                Log::error('Failed to load Stripe payout info.', [
                    'user_id' => $user->id,
                    'stripe_account_id' => $user->stripe_account_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('dashboard.withdraw', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
            'payoutsReady' => $payoutsReady,
        ]);
    }

    /**
     * Process withdrawal.
     *
     * Flow:
     *
     * Saturn local balance
     *        ↓
     * Stripe Platform balance
     *        ↓
     * Transfer
     *        ↓
     * Connected Account
     *        ↓
     * Payout
     *        ↓
     * User bank account
     *
     * IMPORTANT:
     * The transfer and payout are NOT one atomic Stripe operation.
     * Therefore we save the transfer ID immediately after the transfer
     * succeeds, before attempting the payout.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10', 'max:100000'],
            'external_account_id' => ['required', 'string'],
        ]);

        $user = Auth::user();

        $amount = round((float) $validated['amount'], 2);
        $amountInCents = (int) round($amount * 100);

        if (empty($user->stripe_account_id)) {
            return back()->withErrors([
                'withdrawal' => 'Your payout account is not set up yet.',
            ]);
        }

        $stripe = new StripeClient(
            config('services.stripe.secret')
        );

        /*
        |--------------------------------------------------------------------------
        | 1. Verify connected account
        |--------------------------------------------------------------------------
        */

        try {
            $account = $stripe->v2->core->accounts->retrieve(
                $user->stripe_account_id,
                [
                    'include' => ['configuration.recipient'],
                ]
            );

            $status =
                $account->configuration->recipient->capabilities
                    ->stripe_balance->stripe_transfers->status
                ?? null;

            if ($status !== 'active') {
                return back()->withErrors([
                    'withdrawal' =>
                        'Your payout account is not yet fully verified with Stripe.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Verify selected bank account
            |--------------------------------------------------------------------------
            */

            $externalAccount = $stripe->accounts->retrieveExternalAccount(
                $user->stripe_account_id,
                $validated['external_account_id']
            );

            if (
                !$externalAccount ||
                $externalAccount->object !== 'bank_account'
            ) {
                return back()->withErrors([
                    'withdrawal' => 'Selected bank account is invalid.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Check Stripe PLATFORM available balance
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | Transfers are funded from the platform's Stripe balance.
            | "Available" is what matters here, not pending.
            |
            */

            $platformBalance = $stripe->balance->retrieve();

            $availableEur = collect($platformBalance->available)
                ->first(
                    fn ($balance) =>
                        strtolower($balance->currency) === 'eur'
                );

            $availableEurCents = $availableEur->amount ?? 0;

            Log::info('Stripe platform balance checked before withdrawal.', [
                'user_id' => $user->id,
                'amount_eur' => $amount,
                'amount_cents' => $amountInCents,
                'available_eur_cents' => $availableEurCents,
                'available_eur' => $availableEurCents / 100,
                'stripe_account_id' => $user->stripe_account_id,
            ]);

            if ($amountInCents > $availableEurCents) {
                return back()->withErrors([
                    'withdrawal' =>
                        'This amount is not currently available in your Stripe platform balance. Some funds may still be settling.',
                ]);
            }
        } catch (ApiErrorException $e) {
            Log::error('Stripe withdrawal pre-check failed.', [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'amount' => $amount,
                'stripe_error_type' => $e->getError()->type ?? null,
                'stripe_error_code' => $e->getError()->code ?? null,
                'stripe_error_message' => $e->getError()->message ?? null,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'withdrawal' =>
                    'Unable to verify your Stripe payout account right now.',
            ]);
        } catch (Throwable $e) {
            Log::error('Withdrawal pre-check failed.', [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'withdrawal' =>
                    'Unable to verify your payout account right now.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Reserve/deduct Saturn balance
        |--------------------------------------------------------------------------
        |
        | We lock the user row so two simultaneous withdrawal requests
        | cannot spend the same Saturn balance.
        |
        */

        try {
            $transaction = DB::transaction(function () use (
                $user,
                $amount,
                $externalAccount
            ) {
                $lockedUser = DB::table('users')
                    ->where('id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (!$lockedUser) {
                    throw new \Exception('User not found.');
                }

                if ((float) $lockedUser->balance < $amount) {
                    throw new \Exception(
                        'Insufficient available Saturn balance.'
                    );
                }

                $reference =
                    'WD-' . strtoupper(Str::random(16));

                /*
                 * Deduct Saturn balance BEFORE talking to Stripe.
                 */
                DB::table('users')
                    ->where('id', $user->id)
                    ->decrement('balance', $amount);

                return Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'amount' => $amount,
                    'status' => 'pending',
                    'reference' => $reference,
                    'description' =>
                        'Withdrawal to ' .
                        $externalAccount->bank_name .
                        ' ending in ' .
                        $externalAccount->last4,
                ]);
            });
        } catch (Throwable $e) {
            Log::error('Failed to reserve Saturn balance for withdrawal.', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'withdrawal' =>
                    'Withdrawal could not be started: ' .
                    $e->getMessage(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. CREATE STRIPE TRANSFER
        |--------------------------------------------------------------------------
        |
        | THIS IS THE CRITICAL PART.
        |
        | We save stripe_transfer_id immediately after this succeeds.
        |
        | Previously your code waited until AFTER the payout succeeded.
        | That meant:
        |
        | transfer succeeds
        | payout fails
        | Laravel thinks transfer never happened
        | Saturn balance gets restored
        |
        | That can create a serious accounting mismatch.
        |
        */

        try {
            Log::info('Creating Stripe transfer.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'amount_cents' => $amountInCents,
                'currency' => 'eur',
                'destination' => $user->stripe_account_id,
            ]);

            $debugBalance = $stripe->balance->retrieve();

Log::info('========== STRIPE WITHDRAWAL DEBUG ==========', [
    'platform_balance_available' => $debugBalance->available,
    'platform_balance_pending' => $debugBalance->pending,
    'platform_balance_connect_reserved' => $debugBalance->connect_reserved ?? null,
    'platform_balance_instant_available' => $debugBalance->instant_available ?? null,

    'withdrawal_amount_cents' => $amountInCents,
    'withdrawal_currency' => 'eur',

    'destination_account' => $user->stripe_account_id,

    'transaction_id' => $transaction->id,

    'external_account_id' => $externalAccount->id,
    'external_account_currency' => $externalAccount->currency ?? null,
    'external_account_country' => $externalAccount->country ?? null,
]);

            $transfer = $stripe->transfers->create([
                'amount' => $amountInCents,
                'currency' => 'eur',
                'destination' => $user->stripe_account_id,
                'description' =>
                    'Saturn withdrawal ' .
                    $transaction->reference,
                'metadata' => [
                    'transaction_id' => (string) $transaction->id,
                    'user_id' => (string) $user->id,
                    'withdrawal_reference' =>
                        $transaction->reference,
                ],
            ]);

            /*
             * SAVE THE TRANSFER IMMEDIATELY.
             */
            $transaction->update([
                'stripe_transfer_id' => $transfer->id,
                'status' => 'transfer_created',
            ]);

            Log::info('Stripe transfer created successfully.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'stripe_transfer_id' => $transfer->id,
                'transfer_amount' => $transfer->amount,
                'transfer_currency' => $transfer->currency,
                'transfer_destination' => $transfer->destination ?? null,
                'transfer_created' => $transfer->created ?? null,
            ]);
        } catch (ApiErrorException $e) {
            /*
             * TRANSFER FAILED.
             *
             * Since there is no transfer ID, it is safe to restore
             * Saturn's local balance.
             */

            Log::error('Stripe TRANSFER creation failed.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'amount_cents' => $amountInCents,
                'stripe_error_type' =>
                    $e->getError()->type ?? null,
                'stripe_error_code' =>
                    $e->getError()->code ?? null,
                'stripe_error_param' =>
                    $e->getError()->param ?? null,
                'stripe_error_message' =>
                    $e->getError()->message ?? null,
                'message' => $e->getMessage(),
            ]);

            try {
                DB::transaction(function () use ($transaction) {
                    $lockedTransaction = Transaction::where(
                        'id',
                        $transaction->id
                    )
                        ->lockForUpdate()
                        ->first();

                    if (
                        $lockedTransaction &&
                        empty($lockedTransaction->stripe_transfer_id) &&
                        $lockedTransaction->status === 'pending'
                    ) {
                        DB::table('users')
                            ->where(
                                'id',
                                $lockedTransaction->user_id
                            )
                            ->increment(
                                'balance',
                                $lockedTransaction->amount
                            );

                        $lockedTransaction->update([
                            'status' => 'failed',
                        ]);
                    }
                });
            } catch (Throwable $rollbackError) {
                Log::critical(
                    'CRITICAL: Failed to restore balance after Stripe transfer failure.',
                    [
                        'transaction_id' => $transaction->id,
                        'error' => $rollbackError->getMessage(),
                    ]
                );
            }

            return back()->withErrors([
                'withdrawal' =>
                    'Stripe could not create the transfer: ' .
                    ($e->getError()->message ??
                        $e->getMessage()),
            ]);
        } catch (Throwable $e) {
            /*
             * Unknown transfer error.
             *
             * Do NOT automatically refund the Saturn balance here.
             *
             * An unknown/network error could mean Stripe created the
             * transfer but our application did not receive the response.
             *
             * This is important for avoiding double withdrawals.
             */

            Log::critical(
                'UNKNOWN ERROR during Stripe transfer creation. Balance NOT automatically restored.',
                [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                    'error' => $e->getMessage(),
                ]
            );

            $transaction->update([
                'status' => 'transfer_unknown',
            ]);

            return back()->withErrors([
                'withdrawal' =>
                    'The transfer could not be confirmed. Your balance has been reserved while Stripe is checked. Please contact support if this persists.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. CREATE PAYOUT ON CONNECTED ACCOUNT
        |--------------------------------------------------------------------------
        |
        | The transfer has already succeeded at this point.
        |
        | If this operation fails, DO NOT restore the Saturn balance.
        |
        | Why?
        |
        | Because the €10 has already left the platform and reached
        | the connected account.
        |
        */

        try {
            Log::info('Creating Stripe payout on connected account.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'stripe_transfer_id' => $transfer->id,
                'stripe_account_id' => $user->stripe_account_id,
                'external_account_id' => $externalAccount->id,
                'amount' => $amount,
                'amount_cents' => $amountInCents,
            ]);

            $payout = $stripe->payouts->create(
                [
                    'amount' => $amountInCents,
                    'currency' => 'eur',
                    'destination' => $externalAccount->id,
                    'description' =>
                        'Saturn withdrawal ' .
                        $transaction->reference,
                    'metadata' => [
                        'transaction_id' =>
                            (string) $transaction->id,
                        'user_id' =>
                            (string) $user->id,
                        'withdrawal_reference' =>
                            $transaction->reference,
                        'stripe_transfer_id' =>
                            $transfer->id,
                    ],
                ],
                [
                    'stripe_account' =>
                        $user->stripe_account_id,
                ]
            );

            /*
             * SAVE PAYOUT ID.
             */
            $transaction->update([
                'stripe_payout_id' => $payout->id,
                'status' => 'payout_created',
            ]);

            Log::info('Stripe payout created successfully.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'stripe_transfer_id' => $transfer->id,
                'stripe_payout_id' => $payout->id,
                'payout_status' => $payout->status ?? null,
                'payout_amount' => $payout->amount ?? null,
                'payout_currency' => $payout->currency ?? null,
                'payout_destination' =>
                    $payout->destination ?? null,
            ]);

            return back()->with(
                'success',
                'Withdrawal submitted successfully. Your bank transfer is being processed.'
            );
        } catch (ApiErrorException $e) {
            /*
             * VERY IMPORTANT:
             *
             * The transfer already succeeded.
             *
             * Therefore we DO NOT restore Saturn balance here.
             *
             * Instead, leave the transaction associated with the
             * successful transfer and mark the payout as failed.
             */

            Log::error('Stripe PAYOUT creation failed.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'stripe_transfer_id' => $transfer->id,
                'stripe_account_id' => $user->stripe_account_id,
                'amount' => $amount,
                'stripe_error_type' =>
                    $e->getError()->type ?? null,
                'stripe_error_code' =>
                    $e->getError()->code ?? null,
                'stripe_error_param' =>
                    $e->getError()->param ?? null,
                'stripe_error_message' =>
                    $e->getError()->message ?? null,
                'message' => $e->getMessage(),
            ]);

            $transaction->update([
                'status' => 'payout_failed',
            ]);

            return back()->withErrors([
                'withdrawal' =>
                    'The Stripe transfer was created successfully, but the bank payout could not be created: ' .
                    ($e->getError()->message ??
                        $e->getMessage()),
            ]);
        } catch (Throwable $e) {
            /*
             * Again, DO NOT refund the Saturn balance automatically.
             *
             * The transfer has already happened.
             */

            Log::critical(
                'UNKNOWN ERROR during Stripe payout creation. Transfer already exists.',
                [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->id,
                    'stripe_transfer_id' => $transfer->id,
                    'stripe_account_id' => $user->stripe_account_id,
                    'amount' => $amount,
                    'error' => $e->getMessage(),
                ]
            );

            $transaction->update([
                'status' => 'payout_unknown',
            ]);

            return back()->withErrors([
                'withdrawal' =>
                    'The Stripe transfer was created, but the payout could not be confirmed. Please check Stripe before retrying.',
            ]);
        }
    }
}