<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Throwable;

class AccountController extends Controller
{
    /**
     * Show account page.
     */
    public function index()
    {
        $user = Auth::user();

        $stripeBankAccounts = [];
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
        $stripeBankAccounts[] = [
            'id' => $ext->id,
            'bank_name' => $ext->bank_name ?: 'Bank account',
            'last4' => $ext->last4,
            'currency' => strtoupper($ext->currency),
            'default_for_currency' => (bool) $ext->default_for_currency,
        ];
    }
}
            } catch (Throwable $e) {
                Log::error('Failed to load Stripe payout info in AccountController.', [
                    'user_id' => $user->id,
                    'stripe_account_id' => $user->stripe_account_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        /*
         * Load the user's local bank accounts.
         *
         * These are legacy/local records. Stripe is now the
         * source of truth for the actual recipient bank account.
         */
        $bankAccounts = BankAccount::where('user_id', $user->id)
            ->latest()
            ->get();

        /*
         * Load all active Laravel sessions belonging to this user.
         */
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get();

        return view('dashboard.account', [
            'user' => $user,
            'bankAccounts' => $bankAccounts,
            'stripeBankAccounts' => $stripeBankAccounts,
            'payoutsReady' => $payoutsReady,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Update profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],
            'dob' => [
                'nullable',
                'date',
            ],
            'country' => [
                'nullable',
                'string',
                'max:100',
            ],
            'address' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        /*
         * These fields exist in your current account Blade.
         * Only assign them if your users table contains them.
         */
        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }

        if (array_key_exists('dob', $validated)) {
            $user->dob = $validated['dob'];
        }

        if (array_key_exists('country', $validated)) {
            $user->country = $validated['country'];
        }

        if (array_key_exists('address', $validated)) {
            $user->address = $validated['address'];
        }

        $user->save();

        return back()->with(
            'success',
            'Profile updated successfully.'
        );
    }

    /**
     * Update account password.
     */
    public function updatePassword(Request $request)
    {
        /*
         * Your Blade uses:
         *
         * new_password
         * new_password_confirmation
         *
         * so the controller must validate those names.
         */
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'new_password' => [
                'required',
                'confirmed',
                'min:8',
            ],
        ]);

        $user = Auth::user();

        $user->password = Hash::make(
            $validated['new_password']
        );

        $user->save();

        return back()->with(
            'success',
            'Password updated successfully.'
        );
    }

    /**
 * Start Stripe recipient onboarding.
 *
 * Stripe hosts the onboarding flow and collects the
 * recipient's identity and bank-account information.
 *
 * Saturn does NOT collect the raw IBAN.
 */
public function startStripeOnboarding()
{
    $user = Auth::user();

    try {
        $stripe = new StripeClient(
            config('services.stripe.secret')
        );

        /*
         * Create the Stripe recipient account only once.
         */
        if (!$user->stripe_account_id) {

        $country = strtolower($user->country ?? 'RO');

            $account = $stripe->v2->core->accounts->create([
                'contact_email' => $user->email,

                'dashboard' => 'none',

                'identity' => [
                    'country' => $country,
                ],

                'defaults' => [
                    'responsibilities' => [
                        'losses_collector' => 'application',
                        'fees_collector' => 'application',
                    ],
                ],

                'configuration' => [
                    'recipient' => [
                        'capabilities' => [
                            'stripe_balance' => [
                                'stripe_transfers' => [
                                    'requested' => true,
                                ],
                            ],
                        ],
                    ],
                ],

                'metadata' => [
                    'saturn_user_id' => (string) $user->id,
                ],

                'include' => [
                    'configuration.recipient',
                ],
            ]);

            /*
             * Store the Stripe connected account ID
             * in Saturn's users table.
             */
            $user->stripe_account_id = $account->id;
            $user->save();
        }

        /*
         * Create a Stripe-hosted onboarding link.
         */
        $accountLink = $stripe->v2->core->accountLinks->create([
            'account' => $user->stripe_account_id,

            'use_case' => [
                'type' => 'account_onboarding',

                'account_onboarding' => [
                    'configurations' => [
                        'recipient',
                    ],

                    'refresh_url' => route(
                        'account.stripe.onboarding'
                    ),

                    'return_url' => route(
                        'account.stripe.return'
                    ),
                ],
            ],
        ]);

        /*
         * Send the user to Stripe's hosted onboarding page.
         */
        return redirect()->away(
            $accountLink->url
        );

    } catch (Throwable $e) {

        Log::error(
            'Stripe recipient onboarding failed.',
            [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]
        );

        /*
         * TEMPORARY:
         * Show the actual Stripe error while we are developing.
         */
        return back()->with(
            'error',
            'Stripe error: ' . $e->getMessage()
        );
    }
}

    /**
     * Stripe returns the user here after onboarding.
     */
    public function stripeOnboardingReturn()
    {
        $user = Auth::user();

        if (!$user->stripe_account_id) {
            return redirect()
                ->route('account.index')
                ->with(
                    'error',
                    'Stripe account was not found.'
                );
        }

        try {
            $stripe = new StripeClient(
                config('services.stripe.secret')
            );

            /*
             * Retrieve the account from Stripe.
             */
            $stripe->v2->core->accounts->retrieve(
                $user->stripe_account_id,
                [
                    'include' => [
                        'configuration.recipient',
                    ],
                ]
            );

            Log::info(
                'Stripe recipient onboarding returned.',
                [
                    'user_id' => $user->id,
                    'stripe_account_id' =>
                        $user->stripe_account_id,
                ]
            );

            return redirect()
                ->route('account.index')
                ->with(
                    'success',
                    'Bank account setup was completed through Stripe.'
                );

        } catch (Throwable $e) {

            Log::error(
                'Stripe onboarding return failed.',
                [
                    'user_id' => $user->id,
                    'stripe_account_id' =>
                        $user->stripe_account_id,
                    'error' => $e->getMessage(),
                ]
            );

            return redirect()
                ->route('account.index')
                ->with(
                    'error',
                    'Unable to verify your Stripe account setup.'
                );
        }
    }

    /**
     * Re-create a Stripe onboarding link.
     */
    public function stripeOnboardingRefresh()
    {
        return redirect()->route(
            'account.stripe.onboarding'
        );
    }

    /**
     * Request accreditation.
     */
    public function requestAccreditation()
    {
        $user = Auth::user();

        /*
         * Keep your existing accreditation logic here
         * when you implement it.
         */

        return back()->with(
            'success',
            'Accreditation request submitted.'
        );
    }

    /**
     * Log out another active session.
     */
    public function logoutSession($id)
    {
        $currentSessionId = request()->session()->getId();

        /*
         * Never allow the current session to be destroyed
         * through this endpoint.
         */
        if ($id === $currentSessionId) {
            return back()->with(
                'error',
                'You cannot log out the current session here.'
            );
        }

        /*
         * Only delete sessions belonging to the authenticated user.
         */
        $deleted = DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        if ($deleted === 0) {
            return back()->with(
                'error',
                'Session not found.'
            );
        }

        return back()->with(
            'success',
            'Session logged out.'
        );
    }
}