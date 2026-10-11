<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Throwable;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Illuminate\Support\Str;
use BaconQrCode\Writer;

class AccountController extends Controller
{
    /**
     * Show account page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $twoFactorQr = null;
        $twoFactorSecret = null;
        $stripeBankAccounts = [];
        $payoutsReady = false;

        if (!$user->two_factor_confirmed_at) {
    $google2fa = new Google2FA();

    if (!$user->two_factor_secret) {
        $user->two_factor_secret = encrypt($google2fa->generateSecretKey());
        $user->save();
    }

    $twoFactorSecret = decrypt($user->two_factor_secret);

    $otpUrl = $google2fa->getQRCodeUrl(
        config('app.name'),
        $user->email,
        $twoFactorSecret
    );

    $twoFactorQr = (new Writer(
        new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd())
    ))->writeString($otpUrl);
}

        /*
         * ---------------------------------------------------------
         * Stripe recipient account
         * ---------------------------------------------------------
         */
        if ($user->stripe_account_id) {
            try {
                $stripe = new StripeClient(
                    config('services.stripe.secret')
                );

                $account = $stripe->v2->core->accounts->retrieve(
                    $user->stripe_account_id,
                    [
                        'include' => [
                            'configuration.recipient',
                        ],
                    ]
                );

                $status = $account
                    ->configuration
                    ->recipient
                    ->capabilities
                    ->stripe_balance
                    ->stripe_transfers
                    ->status ?? null;

                $payoutsReady = $status === 'active';


                /*
                 * -------------------------------------------------
                 * External bank accounts
                 * -------------------------------------------------
                 *
                 * Stripe is the source of truth.
                 */
                if ($payoutsReady) {

                    $externalAccounts =
                        $stripe->accounts->allExternalAccounts(
                            $user->stripe_account_id,
                            [
                                'object' => 'bank_account',
                                'limit' => 10,
                            ]
                        );


                    foreach ($externalAccounts->data as $ext) {

                        $stripeBankAccounts[] = [
                            'id' => $ext->id,

                            'bank_name' =>
                                $ext->bank_name ?: 'Bank account',

                            'last4' =>
                                $ext->last4,

                            'currency' =>
                                strtoupper($ext->currency),

                            'default_for_currency' =>
                                (bool) $ext->default_for_currency,
                        ];
                    }
                }

            } catch (Throwable $e) {

                Log::error(
                    'Failed to load Stripe payout info in AccountController.',
                    [
                        'user_id' =>
                            $user->id,

                        'stripe_account_id' =>
                            $user->stripe_account_id,

                        'error' =>
                            $e->getMessage(),
                    ]
                );
            }
        }


        /*
         * ---------------------------------------------------------
         * Local bank accounts
         * ---------------------------------------------------------
         *
         * These are legacy/local records.
         * Stripe is the source of truth for the actual payout
         * bank account.
         */
        $bankAccounts = BankAccount::where(
            'user_id',
            $user->id
        )
            ->latest()
            ->get();


        /*
         * ---------------------------------------------------------
         * Active Laravel sessions
         * ---------------------------------------------------------
         */
        $sessions = DB::table('sessions')
            ->where(
                'user_id',
                $user->id
            )
            ->orderByDesc('last_activity')
            ->get();


        /*
         * ---------------------------------------------------------
         * JSON response
         * ---------------------------------------------------------
         */
        if ($request->wantsJson()) {

            return response()->json([
                'user' =>
                    $user,

                'bank_accounts' =>
                    $bankAccounts,

                'stripe_bank_accounts' =>
                    $stripeBankAccounts,

                'payouts_ready' =>
                    $payoutsReady,

                'sessions' =>
                    $sessions,
            ]);
        }


        /*
         * ---------------------------------------------------------
         * Blade view
         * ---------------------------------------------------------
         */
        return view(
            'dashboard.account',
            [
                'user' =>
                    $user,

                'bankAccounts' =>
                    $bankAccounts,

                'stripeBankAccounts' =>
                    $stripeBankAccounts,

                'payoutsReady' =>
                    $payoutsReady,

                'sessions' =>
                    $sessions,

                'twoFactorQr' => $twoFactorQr,
                'twoFactorSecret' => $twoFactorSecret,
            ]
        );
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


        $user->name =
            $validated['name'];

        $user->email =
            $validated['email'];


        /*
         * These fields are present in the Blade.
         */
        if (array_key_exists('phone', $validated)) {

            $user->phone =
                $validated['phone'];
        }


        if (array_key_exists('dob', $validated)) {

            $user->dob =
                $validated['dob'];
        }


        if (array_key_exists('country', $validated)) {

            $user->country =
                $validated['country'];
        }


        if (array_key_exists('address', $validated)) {

            $user->address =
                $validated['address'];
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
     * Stripe hosts the onboarding flow and collects
     * the recipient's required information and bank account.
     *
     * Saturn does not collect the raw IBAN.
     */
    public function startStripeOnboarding()
    {
        $user = Auth::user();


        try {

            $stripe = new StripeClient(
                config('services.stripe.secret')
            );


            /*
             * -----------------------------------------------------
             * Create Stripe recipient account once.
             * -----------------------------------------------------
             */
            if (!$user->stripe_account_id) {

                /*
                 * Stripe expects a two-letter ISO country code.
                 */
                $country = strtoupper(
                    $user->country ?? 'RO'
                );


                /*
                 * If the database contains "Romania",
                 * convert it to RO.
                 */
                $countryMap = [
                    'ROMANIA' => 'RO',
                    'ITALY' => 'IT',
                    'FRANCE' => 'FR',
                    'SPAIN' => 'ES',
                ];


                if (isset($countryMap[$country])) {

                    $country =
                        $countryMap[$country];
                }


                /*
                 * Safety fallback.
                 */
                if (!preg_match('/^[A-Z]{2}$/', $country)) {

                    $country = 'RO';
                }


                $account =
                    $stripe->v2->core->accounts->create([
                        'contact_email' =>
                            $user->email,

                        'dashboard' =>
                            'none',

                        'identity' => [
                            'country' =>
                                $country,
                        ],

                        'defaults' => [
                            'responsibilities' => [
                                'losses_collector' =>
                                    'application',

                                'fees_collector' =>
                                    'application',
                            ],
                        ],

                        'configuration' => [
                            'recipient' => [
                                'capabilities' => [
                                    'stripe_balance' => [
                                        'stripe_transfers' => [
                                            'requested' =>
                                                true,
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        'metadata' => [
                            'saturn_user_id' =>
                                (string) $user->id,
                        ],

                        'include' => [
                            'configuration.recipient',
                        ],
                    ]);


                /*
                 * Save Stripe account ID.
                 */
                $user->stripe_account_id =
                    $account->id;

                $user->save();
            }


            /*
             * -----------------------------------------------------
             * Create / refresh Stripe hosted onboarding link.
             * -----------------------------------------------------
             */
            $accountLink =
                $stripe->v2->core->accountLinks->create([
                    'account' =>
                        $user->stripe_account_id,

                    'use_case' => [
                        'type' =>
                            'account_onboarding',

                        'account_onboarding' => [
                            'configurations' => [
                                'recipient',
                            ],

                            'refresh_url' =>
                                route(
                                    'account.stripe.onboarding'
                                ),

                            'return_url' =>
                                route(
                                    'account.stripe.return'
                                ),
                        ],
                    ],
                ]);


            /*
             * Send user to Stripe.
             */
            return redirect()->away(
                $accountLink->url
            );

        } catch (Throwable $e) {

            Log::error(
                'Stripe recipient onboarding failed.',
                [
                    'user_id' =>
                        $user->id,

                    'stripe_account_id' =>
                        $user->stripe_account_id,

                    'exception_class' =>
                        get_class($e),

                    'error' =>
                        $e->getMessage(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );


            return back()->with(
                'error',
                'Stripe error: ' . $e->getMessage()
            );
        }
    }


    /**
     * Stripe returns the user here after onboarding.
     *
     * IMPORTANT:
     * Returning from Stripe does NOT automatically mean
     * the bank account and transfers capability are ready.
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
             * Retrieve the Stripe recipient account.
             */
            $account =
                $stripe->v2->core->accounts->retrieve(
                    $user->stripe_account_id,
                    [
                        'include' => [
                            'configuration.recipient',
                        ],
                    ]
                );


            /*
             * Check the actual transfers capability.
             */
            $status =
                $account
                    ->configuration
                    ->recipient
                    ->capabilities
                    ->stripe_balance
                    ->stripe_transfers
                    ->status
                    ?? null;


            /*
             * Check actual external bank accounts.
             */
            $externalAccounts =
                $stripe->accounts->allExternalAccounts(
                    $user->stripe_account_id,
                    [
                        'object' => 'bank_account',
                        'limit' => 10,
                    ]
                );


            $hasBankAccount =
                count($externalAccounts->data) > 0;


            Log::info(
                'Stripe recipient onboarding returned.',
                [
                    'user_id' =>
                        $user->id,

                    'stripe_account_id' =>
                        $user->stripe_account_id,

                    'transfers_status' =>
                        $status,

                    'has_bank_account' =>
                        $hasBankAccount,
                ]
            );


            /*
             * Stripe onboarding returned but
             * no bank account was attached.
             */
            if (!$hasBankAccount) {

                return redirect()
                    ->route('account.index')
                    ->with(
                        'error',
                        'Stripe setup was opened, but no bank account was connected.'
                    );
            }


            /*
             * Bank exists but transfers are not active.
             */
            if ($status !== 'active') {

                return redirect()
                    ->route('account.index')
                    ->with(
                        'error',
                        'Your bank account was added, but Stripe has not enabled withdrawals yet.'
                    );
            }


            /*
             * Everything is ready.
             */
            return redirect()
                ->route('account.index')
                ->with(
                    'success',
                    'Bank account connected successfully. Withdrawals are now available.'
                );

        } catch (Throwable $e) {

            Log::error(
                'Stripe onboarding return failed.',
                [
                    'user_id' =>
                        $user->id,

                    'stripe_account_id' =>
                        $user->stripe_account_id,

                    'error' =>
                        $e->getMessage(),
                ]
            );


            return redirect()
                ->route('account.index')
                ->with(
                    'error',
                    'Unable to verify your Stripe bank-account setup.'
                );
        }
    }


    /**
     * Refresh Stripe onboarding.
     */
    public function stripeOnboardingRefresh()
    {
        return redirect()->route(
            'account.stripe.onboarding'
        );
    }


    /**
     * Start Stripe Identity verification.
     */
    public function startIdentityVerification(
        Request $request
    ) {
        $user = Auth::user();


        try {

            $stripe = new StripeClient(
                config('services.stripe.secret')
            );


            /*
             * Create Stripe Identity session.
             */
            $session =
                $stripe
                    ->identity
                    ->verificationSessions
                    ->create([
                        'type' =>
                            'document',

                        'client_reference_id' =>
                            (string) $user->id,

                        'provided_details' => [
                            'email' =>
                                $user->email,

                            'phone' =>
                                $user->phone ?: null,
                        ],

                        'options' => [
                            'document' => [
                                'require_matching_selfie' =>
                                    true,
                            ],
                        ],

                        'metadata' => [
                            'saturn_user_id' =>
                                (string) $user->id,
                        ],

                        'return_url' =>
                            route('account.index'),
                    ]);


            /*
             * Store the session ID.
             */
            $user->stripe_identity_session_id =
                $session->id;

            $user->save();


            /*
             * JSON for Flutter/API consumers.
             */
            if ($request->wantsJson()) {

                return response()->json([
                    'url' =>
                        $session->url,
                ]);
            }


            /*
             * Browser flow.
             */
            return redirect()->away(
                $session->url
            );

        } catch (Throwable $e) {

            Log::error(
                'Stripe Identity verification session creation failed.',
                [
                    'user_id' =>
                        $user->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );


            if ($request->wantsJson()) {

                return response()->json([
                    'message' =>
                        'Could not start identity verification.',
                ], 500);
            }


            return back()->with(
                'error',
                'Could not start identity verification: ' .
                $e->getMessage()
            );
        }
    }


    /**
     * Request accreditation.
     */
    public function requestAccreditation()
    {
        $user = Auth::user();


        /*
         * Keep existing accreditation logic here
         * when implemented.
         */


        return back()->with(
            'success',
            'Accreditation request submitted.'
        );
    }

/**
 * Confirm and enable 2FA with a valid code.
 */
public function enableTwoFactor(Request $request)
{
    $request->validate(['code' => ['required', 'digits:6']]);

    $user = Auth::user();

    if (!$user->two_factor_secret) {
        return back()->with('error', 'Two-factor setup was not started.');
    }

    // Fixed instantiation using the container service
    $valid = app('pragmarx.google2fa')->verifyKey(
        decrypt($user->two_factor_secret),
        $request->code
    );

    if (!$valid) {
        return back()->with('error', 'Invalid authentication code.')->with('open_2fa', true);
    }

    // Generate raw recovery codes to show the user ONCE
    $rawCodes = \Illuminate\Support\Collection::times(8, fn () => \Illuminate\Support\Str::random(10) . '-' . \Illuminate\Support\Str::random(10))->all();

    $user->two_factor_confirmed_at = now();
    $user->two_factor_recovery_codes = encrypt(json_encode($rawCodes));
    $user->save();

    return back()
        ->with('success', 'Two-factor authentication has been enabled.')
        ->with('recovery_codes', $rawCodes);
}

/**
 * Disable 2FA.
 */
public function disableTwoFactor(Request $request)
{
    $user = Auth::user();
    
    $user->forceFill([
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
    ])->save();

    return back()->with('success', 'Two-factor authentication has been disabled.');
}


    /**
     * Log out another active session.
     */
    public function logoutSession($id)
    {
        $currentSessionId =
            request()->session()->getId();


        /*
         * Never allow the current session
         * to be destroyed through this endpoint.
         */
        if ($id === $currentSessionId) {

            return back()->with(
                'error',
                'You cannot log out the current session here.'
            );
        }


        /*
         * Only delete sessions belonging
         * to the authenticated user.
         */
        $deleted =
            DB::table('sessions')
                ->where(
                    'id',
                    $id
                )
                ->where(
                    'user_id',
                    Auth::id()
                )
                ->delete();


        if (!$deleted) {

            return back()->with(
                'error',
                'Session not found or already logged out.'
            );
        }


        return back()->with(
            'success',
            'Session logged out successfully.'
        );
    }
}