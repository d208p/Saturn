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
                    ['include' => ['configuration.recipient']]
                );

                $status = $account->configuration->recipient->capabilities
                    ->stripe_balance->stripe_transfers->status ?? null;

                $payoutsReady = $status === 'active';

                if ($payoutsReady) {
                    $externalAccounts = $stripe->accounts->allExternalAccounts(
                        $user->stripe_account_id,
                        ['object' => 'bank_account', 'limit' => 10]
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
     * Process a withdrawal.
     *
     * Saturn's ledger is EUR. The connected account may settle in a
     * different currency (here: RON), converted automatically by Stripe.
     *
     * IMPORTANT ARCHITECTURE NOTE — read before touching this again:
     *
     * This controller does NOT call Payout::create(). It never should,
     * for this account. Here's why:
     *
     * The connected account (acct_1UIskB...) only has a RON bank account.
     * Stripe therefore treats RON as its only settlement currency. When
     * this platform transfers EUR into it, Stripe auto-converts those
     * funds toward RON as they settle — there is no "available EUR"
     * balance bucket on the connected account to manually pay out from,
     * regardless of how much EUR the platform has sent it. Any manual
     * payout attempt specifying currency=eur will always report
     * insufficient funds, because from the connected account's own
     * perspective there genuinely is nothing payable in EUR.
     *
     * The connected account ALREADY has an automatic payout schedule
     * (visible on its Stripe dashboard page as "Daily — 7 day rolling
     * basis"). Stripe converts and pays out settled funds to the RON
     * bank account on that schedule without any action from this app —
     * confirmed by the "In transit to bank" line that appears on the
     * account after a transfer settles.
     *
     * So: this app's job stops at creating the Transfer. Completion is
     * tracked via the `payout.paid` / `payout.failed` Connect webhook
     * events (fired on the connected account), which should update the
     * Transaction status. StripeWebHookController needs the payout.*
     * cases added — not wired up yet as of this version, so status will
     * stay "transfer_created" until that's done.
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
            return back()->withErrors(['withdrawal' => 'Your payout account is not set up yet.']);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        /*
        |--------------------------------------------------------------------------
        | 1. Verify connected account + selected bank account
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
                return back()->withErrors([
                    'withdrawal' => 'Your payout account is not yet fully verified with Stripe.',
                ]);
            }

            $externalAccount = $stripe->accounts->retrieveExternalAccount(
                $user->stripe_account_id,
                $validated['external_account_id']
            );

            if (!$externalAccount || $externalAccount->object !== 'bank_account') {
                return back()->withErrors(['withdrawal' => 'Selected bank account is invalid.']);
            }
        } catch (ApiErrorException $e) {
            Log::error('Stripe withdrawal pre-check failed.', [
                'user_id' => $user->id,
                'stripe_error_message' => $e->getError()->message ?? null,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors(['withdrawal' => 'Unable to verify your Stripe payout account right now.']);
        } catch (Throwable $e) {
            Log::error('Withdrawal pre-check failed.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return back()->withErrors(['withdrawal' => 'Unable to verify your payout account right now.']);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Check Stripe PLATFORM available EUR balance (funds the Transfer)
        |--------------------------------------------------------------------------
        */
        try {
            $platformBalance = $stripe->balance->retrieve();

            $availableEur = collect($platformBalance->available)
                ->first(fn ($b) => strtolower($b->currency) === 'eur');

            $availableEurCents = $availableEur->amount ?? 0;

            Log::info('Stripe platform balance checked before withdrawal.', [
                'user_id' => $user->id,
                'required_cents' => $amountInCents,
                'available_eur_cents' => $availableEurCents,
            ]);

            if ($amountInCents > $availableEurCents) {
                return back()->withErrors([
                    'withdrawal' => 'This amount is not currently available in the platform balance. Some funds may still be settling.',
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Platform balance check failed.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return back()->withErrors(['withdrawal' => 'Unable to verify available funds right now.']);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Reserve Saturn balance
        |--------------------------------------------------------------------------
        */
        try {
            $transaction = DB::transaction(function () use ($user, $amount, $externalAccount) {
                $lockedUser = DB::table('users')->where('id', $user->id)->lockForUpdate()->first();

                if (!$lockedUser) {
                    throw new \Exception('User not found.');
                }

                if ((float) $lockedUser->balance < $amount) {
                    throw new \Exception('Insufficient available Saturn balance.');
                }

                $reference = 'WD-' . strtoupper(Str::random(16));

                DB::table('users')->where('id', $user->id)->decrement('balance', $amount);

                return Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'amount' => $amount,
                    'status' => 'pending',
                    'reference' => $reference,
                    'description' => 'Withdrawal to ' . $externalAccount->bank_name . ' ending in ' . $externalAccount->last4,
                ]);
            });
        } catch (Throwable $e) {
            Log::error('Failed to reserve Saturn balance.', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return back()->withErrors(['withdrawal' => 'Withdrawal could not be started: ' . $e->getMessage()]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Create the Stripe Transfer — and STOP THERE
        |--------------------------------------------------------------------------
        |
        | No Payout::create() call. See the class docblock above for why.
        | The connected account's own automatic payout schedule takes it
        | from here. This app's remaining job is to reflect that via
        | webhooks once StripeWebHookController handles payout.paid /
        | payout.failed for this connected account.
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
            ]);

            $transaction->update([
                'stripe_transfer_id' => $transfer->id,
                // Completed by the payout.paid webhook once wired up.
                'status' => 'transfer_created',
            ]);

            Log::info("Stripe transfer created successfully. Payout will be handled by the account's automatic schedule.", [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'stripe_transfer_id' => $transfer->id,
            ]);

            return back()->with(
                'success',
                'Withdrawal submitted successfully. Funds will settle and be paid out to your bank automatically within a few days.'
            );
        } catch (ApiErrorException $e) {
            // No transfer id was ever saved — safe to restore the balance.
            Log::error('Stripe TRANSFER creation failed.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'stripe_error_message' => $e->getError()->message ?? null,
                'message' => $e->getMessage(),
            ]);

            try {
                DB::transaction(function () use ($transaction) {
                    $lockedTransaction = Transaction::where('id', $transaction->id)->lockForUpdate()->first();

                    if ($lockedTransaction && empty($lockedTransaction->stripe_transfer_id) && $lockedTransaction->status === 'pending') {
                        DB::table('users')->where('id', $lockedTransaction->user_id)->increment('balance', $lockedTransaction->amount);
                        $lockedTransaction->update(['status' => 'failed']);
                    }
                });
            } catch (Throwable $rollbackError) {
                Log::critical('CRITICAL: Failed to restore balance after transfer failure.', [
                    'transaction_id' => $transaction->id,
                    'error' => $rollbackError->getMessage(),
                ]);
            }

            return back()->withErrors([
                'withdrawal' => 'Stripe could not create the transfer: ' . ($e->getError()->message ?? $e->getMessage()),
            ]);
        } catch (Throwable $e) {
            // Unknown/network error — do NOT auto-restore; avoid double withdrawals.
            Log::critical('UNKNOWN ERROR during Stripe transfer creation.', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            $transaction->update(['status' => 'transfer_unknown']);

            return back()->withErrors([
                'withdrawal' => 'The transfer could not be confirmed. Your withdrawal has been placed on hold while it is checked. Please contact support if this persists.',
            ]);
        }
    }
}