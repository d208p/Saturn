<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Throwable;

class ApiWithdrawalController extends Controller
{
    /**
     * Return withdrawal information and connected Stripe bank accounts.
     *
     * GET /api/withdrawal
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $bankAccounts = [];
        $payoutsReady = false;

        if ($user->stripe_account_id) {
            try {
                $stripe = new StripeClient(config('services.stripe.secret'));

                $account = $stripe->v2->core->accounts->retrieve(
                    $user->stripe_account_id,
                    ['include' => ['configuration.recipient']]
                );

                $status = $account->configuration->recipient->capabilities
                    ->stripe_balance->stripe_transfers->status ?? null;

                $payoutsReady = $status === 'active';

                if ($payoutsReady) {
                    $externalAccounts = $stripe->accounts->allExternalAccounts(
                        $user->stripe_account_id,
                        [
                            'object' => 'bank_account',
                            'limit' => 10,
                        ]
                    );

                    foreach ($externalAccounts->data as $externalAccount) {
                        $bankAccounts[] = [
                            'id' => $externalAccount->id,
                            'bank_name' => $externalAccount->bank_name ?: 'Bank account',
                            'last4' => $externalAccount->last4,
                            'currency' => strtoupper($externalAccount->currency),
                            'default_for_currency' => (bool) $externalAccount->default_for_currency,
                        ];
                    }
                }
            } catch (Throwable $e) {
                Log::error('Failed to load Stripe withdrawal information.', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'balance' => (float) ($user->balance ?? 0),
            'stripe_account_connected' => !empty($user->stripe_account_id),
            'payouts_ready' => $payoutsReady,
            'bank_accounts' => $bankAccounts,
            'minimum_withdrawal' => 10,
            'maximum_withdrawal' => 100000,
            'currency' => 'EUR',
        ]);
    }

    /**
     * Process a withdrawal.
     *
     * POST /api/withdrawal
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10', 'max:100000'],
            'external_account_id' => ['required', 'string'],
        ]);

        $user = $request->user();

        $amount = round((float) $validated['amount'], 2);
        $amountInCents = (int) round($amount * 100);

        if (empty($user->stripe_account_id)) {
            return response()->json([
                'message' => 'Your Stripe payout account is not set up yet.',
                'code' => 'stripe_account_missing',
            ], 422);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        /*
        |--------------------------------------------------------------------------
        | 1. Verify connected account and selected bank account
        |--------------------------------------------------------------------------
        */

        try {
            $account = $stripe->v2->core->accounts->retrieve(
                $user->stripe_account_id,
                ['include' => ['configuration.recipient']]
            );

            $status = $account->configuration->recipient->capabilities
                ->stripe_balance->stripe_transfers->status ?? null;

            if ($status !== 'active') {
                return response()->json([
                    'message' => 'Your payout account is not yet fully verified with Stripe.',
                    'code' => 'stripe_account_not_ready',
                ], 422);
            }

            $externalAccount = $stripe->accounts->retrieveExternalAccount(
                $user->stripe_account_id,
                $validated['external_account_id']
            );

            if (!$externalAccount || $externalAccount->object !== 'bank_account') {
                return response()->json([
                    'message' => 'Selected bank account is invalid.',
                    'code' => 'invalid_bank_account',
                ], 422);
            }
        } catch (ApiErrorException $e) {
            Log::error('Stripe withdrawal pre-check failed.', [
                'user_id' => $user->id,
                'stripe_error_message' => $e->getError()->message ?? null,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to verify your Stripe payout account right now.',
                'code' => 'stripe_verification_failed',
            ], 502);
        } catch (Throwable $e) {
            Log::error('Withdrawal pre-check failed.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to verify your payout account right now.',
                'code' => 'payout_verification_failed',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Check Stripe platform available EUR balance
        |--------------------------------------------------------------------------
        */

        try {
            $platformBalance = $stripe->balance->retrieve();

            $availableEur = collect($platformBalance->available)
                ->first(fn ($balance) => strtolower($balance->currency) === 'eur');

            $availableEurCents = $availableEur->amount ?? 0;

            Log::info('Stripe platform balance checked before withdrawal.', [
                'user_id' => $user->id,
                'required_cents' => $amountInCents,
                'available_eur_cents' => $availableEurCents,
            ]);

            if ($amountInCents > $availableEurCents) {
                return response()->json([
                    'message' => 'This amount is not currently available in the platform balance. Some funds may still be settling.',
                    'code' => 'insufficient_stripe_balance',
                ], 422);
            }
        } catch (Throwable $e) {
            Log::error('Platform balance check failed.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to verify available funds right now.',
                'code' => 'stripe_balance_unavailable',
            ], 502);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Reserve Saturn balance
        |--------------------------------------------------------------------------
        */

        try {
            $transaction = DB::transaction(function () use ($user, $amount, $externalAccount) {
                $lockedUser = DB::table('users')
                    ->where('id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (!$lockedUser) {
                    throw new \Exception('User not found.');
                }

                if ((float) $lockedUser->balance < $amount) {
                    throw new \Exception('Insufficient available Saturn balance.');
                }

                $reference = 'WD-' . strtoupper(Str::random(16));

                DB::table('users')
                    ->where('id', $user->id)
                    ->decrement('balance', $amount);

                return Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'amount' => $amount,
                    'status' => 'pending',
                    'reference' => $reference,
                    'description' => 'Withdrawal to ' .
                        $externalAccount->bank_name .
                        ' ending in ' .
                        $externalAccount->last4,
                ]);
            });
        } catch (Throwable $e) {
            Log::error('Failed to reserve Saturn balance.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            $status = str_contains($e->getMessage(), 'Insufficient available')
                ? 422
                : 500;

            return response()->json([
                'message' => $status === 422
                    ? 'Insufficient available Saturn balance.'
                    : 'Withdrawal could not be started.',
                'code' => $status === 422
                    ? 'insufficient_saturn_balance'
                    : 'withdrawal_reservation_failed',
            ], $status);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Create Stripe Transfer
        |--------------------------------------------------------------------------
        |
        | Do NOT create a manual Payout.
        | The connected account's automatic payout schedule handles settlement.
        |
        */

        try {
            $transfer = $stripe->transfers->create([
                'amount' => $amountInCents,
                'currency' => 'eur',
                'destination' => $user->stripe_account_id,
                'description' => 'Saturn withdrawal ' . $transaction->reference,
                'metadata' => [
                    'transaction_id' => (string) $transaction->id,
                    'user_id' => (string) $user->id,
                    'withdrawal_reference' => $transaction->reference,
                ],
            ], [
                'idempotency_key' => $transaction->reference,
            ]);

            $transaction->update([
                'stripe_transfer_id' => $transfer->id,
                'status' => 'transfer_created',
            ]);

            Log::info('Stripe withdrawal transfer created.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'stripe_transfer_id' => $transfer->id,
            ]);

            return response()->json([
                'message' => 'Withdrawal submitted successfully. Funds will settle and be paid out to your bank automatically.',
                'withdrawal' => [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'amount' => $amount,
                    'currency' => 'EUR',
                    'status' => 'transfer_created',
                    'stripe_transfer_id' => $transfer->id,
                    'bank_account' => [
                        'bank_name' => $externalAccount->bank_name ?: 'Bank account',
                        'last4' => $externalAccount->last4,
                    ],
                ],
                'balance' => (float) DB::table('users')
                    ->where('id', $user->id)
                    ->value('balance'),
            ], 202);

        } catch (ApiErrorException $e) {
            Log::error('Stripe transfer creation failed.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'stripe_error_message' => $e->getError()->message ?? null,
                'message' => $e->getMessage(),
            ]);

            try {
                DB::transaction(function () use ($transaction) {
                    $lockedTransaction = Transaction::where('id', $transaction->id)
                        ->lockForUpdate()
                        ->first();

                    if (
                        $lockedTransaction &&
                        empty($lockedTransaction->stripe_transfer_id) &&
                        $lockedTransaction->status === 'pending'
                    ) {
                        DB::table('users')
                            ->where('id', $lockedTransaction->user_id)
                            ->increment('balance', $lockedTransaction->amount);

                        $lockedTransaction->update([
                            'status' => 'failed',
                        ]);
                    }
                });
            } catch (Throwable $rollbackError) {
                Log::critical('Failed to restore balance after Stripe transfer failure.', [
                    'transaction_id' => $transaction->id,
                    'error' => $rollbackError->getMessage(),
                ]);
            }

            return response()->json([
                'message' => 'Stripe could not create the withdrawal transfer.',
                'code' => 'stripe_transfer_failed',
                'transaction_id' => $transaction->id,
            ], 502);

        } catch (Throwable $e) {
            // Unknown outcome: do not restore balance automatically.
            // The transfer may have succeeded despite a network/database error.

            Log::critical('Unknown error during Stripe transfer creation.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            $transaction->update([
                'status' => 'transfer_unknown',
            ]);

            return response()->json([
                'message' => 'The transfer could not be confirmed. Your withdrawal has been placed on hold while it is checked.',
                'code' => 'transfer_status_unknown',
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
            ], 202);
        }
    }
}
