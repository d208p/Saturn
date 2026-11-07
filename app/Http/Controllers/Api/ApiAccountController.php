<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Stripe\StripeClient;
use Throwable;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class ApiAccountController extends Controller
{
    /**
     * Return the data required by the Flutter account screen.
     */
    public function index(Request $request)
    {
        $user = $request->user()->load('bankAccounts');

        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get();

        $currentSessionId = $request->hasSession()
            ? $request->session()->getId()
            : null;

        $stripeBankAccounts = [];
        $payoutsReady = false;
        $stripeAccountStatus = null;

        if ($user->stripe_account_id) {
            try {
                $stripe = $this->stripe();

                $account = $stripe->v2->core->accounts->retrieve(
                    $user->stripe_account_id,
                    ['include' => ['configuration.recipient']]
                );

                $stripeAccountStatus = data_get(
                    $account,
                    'configuration.recipient.capabilities.stripe_balance.stripe_transfers.status'
                );

                $payoutsReady = $stripeAccountStatus === 'active';

                if ($payoutsReady) {
                    $externalAccounts = $stripe->accounts->allExternalAccounts(
                        $user->stripe_account_id,
                        ['object' => 'bank_account', 'limit' => 10]
                    );

                    foreach ($externalAccounts->data as $externalAccount) {
                        $stripeBankAccounts[] = [
                            'id' => $externalAccount->id,
                            'bank_name' => $externalAccount->bank_name ?: 'Bank account',
                            'last4' => $externalAccount->last4,
                            'currency' => strtoupper($externalAccount->currency),
                            'default_for_currency' => (bool) $externalAccount->default_for_currency,
                        ];
                    }
                }
            } catch (Throwable $e) {
                Log::error('Failed to load Stripe payout information in API account endpoint.', [
                    'user_id' => $user->id,
                    'stripe_account_id' => $user->stripe_account_id,
                    'exception_class' => get_class($e),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        /*
         * Match the website's 2FA setup behavior. The secret and QR code
         * are returned only while setup is pending, never after confirmation.
         */
        $twoFactorSetup = null;

        if (!$user->two_factor_confirmed_at) {
            try {
                $google2fa = app('pragmarx.google2fa');

                if (!$user->two_factor_secret) {
                    $user->two_factor_secret = encrypt($google2fa->generateSecretKey());
                    $user->save();
                }

                $secret = decrypt($user->two_factor_secret);

                $otpUrl = $google2fa->getQRCodeUrl(
                    config('app.name'),
                    $user->email,
                    $secret
                );

                $qrSvg = (new Writer(
                    new ImageRenderer(
                        new RendererStyle(200),
                        new SvgImageBackEnd()
                    )
                ))->writeString($otpUrl);

                $twoFactorSetup = [
                    'secret' => $secret,
                    'qr_svg' => $qrSvg,
                ];
            } catch (Throwable $e) {
                Log::error('Failed to generate 2FA setup data.', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'dob' => optional($user->dob)->format('Y-m-d'),
                'country' => $user->country,
                'address' => $user->address,
                'created_at' => optional($user->created_at)->format('M Y'),
                'kyc_status' => $user->kyc_status ?? 'unverified',
                'balance' => (float) ($user->balance ?? 0),
                'identity_verified' => (bool) $user->identity_verified,
                'address_verified' => (bool) $user->address_verified,
                'selfie_verified' => (bool) $user->selfie_verified,
                'accredited_investor' => (bool) $user->accredited_investor,
                'stripe_account_id' => $user->stripe_account_id,
                'stripe_identity_session_id' => $user->stripe_identity_session_id,
            ],
            'bank_accounts' => $user->bankAccounts->map(fn ($bank) => [
                'id' => $bank->id,
                'bank_name' => $bank->bank_name,
                'masked_iban' => $bank->masked_iban
                    ?? ('****' . substr((string) $bank->iban, -4)),
                'currency' => $bank->currency,
                'is_primary' => (bool) $bank->is_primary,
            ])->values(),
            'stripe_bank_accounts' => $stripeBankAccounts,
            'payouts_ready' => $payoutsReady,
            'stripe_transfers_status' => $stripeAccountStatus,
            'sessions' => $sessions->map(fn ($session) => [
                'id' => $session->id,
                'user_agent' => $session->user_agent,
                'ip_address' => $session->ip_address,
                'last_activity' => $session->last_activity,
                'is_current' => $currentSessionId !== null
                    && hash_equals((string) $currentSessionId, (string) $session->id),
            ])->values(),
            'two_factor' => [
                'enabled' => (bool) $user->two_factor_confirmed_at,
                'setup_required' => !$user->two_factor_confirmed_at,
                'setup' => $twoFactorSetup,
            ],
            'documents' => [
                ['title' => 'Q2 2026 Distribution Statement', 'type' => 'Statement', 'date' => 'Jul 5, 2026'],
                ['title' => 'Investment Agreement — Bucharest Hotel', 'type' => 'Contract', 'date' => 'May 12, 2022'],
                ['title' => 'Investment Agreement — Solar Farm #12', 'type' => 'Contract', 'date' => 'Feb 20, 2025'],
                ['title' => '2025 Tax Summary', 'type' => 'Tax', 'date' => 'Jan 31, 2026'],
                ['title' => 'Proof of Identity.pdf', 'type' => 'KYC', 'date' => 'Jan 3, 2022'],
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'dob' => ['nullable', 'date'],
            'country' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        foreach (['phone', 'dob', 'country', 'address'] as $field) {
            if (array_key_exists($field, $validated)) {
                $user->{$field} = $validated[$field];
            }
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->only([
                'id', 'name', 'email', 'phone', 'dob', 'country', 'address',
            ]),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['new_password']),
        ])->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    /**
     * Start Stripe-hosted recipient onboarding.
     * Flutter receives the URL and opens it in a browser/custom tab.
     */
    public function startStripeOnboarding(Request $request)
    {
        $user = $request->user();

        try {
            $stripe = $this->stripe();

            if (!$user->stripe_account_id) {
                $country = $this->stripeCountryCode($user->country);

                $account = $stripe->v2->core->accounts->create([
                    'contact_email' => $user->email,
                    'dashboard' => 'none',
                    'identity' => ['country' => $country],
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
                                    'stripe_transfers' => ['requested' => true],
                                ],
                            ],
                        ],
                    ],
                    'metadata' => ['saturn_user_id' => (string) $user->id],
                    'include' => ['configuration.recipient'],
                ]);

                $user->stripe_account_id = $account->id;
                $user->save();
            }

            $accountLink = $stripe->v2->core->accountLinks->create([
                'account' => $user->stripe_account_id,
                'use_case' => [
                    'type' => 'account_onboarding',
                    'account_onboarding' => [
                        'configurations' => ['recipient'],
                        'refresh_url' => route('account.stripe.onboarding'),
                        'return_url' => route('account.stripe.return'),
                    ],
                ],
            ]);

            return response()->json([
                'message' => 'Stripe onboarding link created.',
                'url' => $accountLink->url,
                'stripe_account_id' => $user->stripe_account_id,
            ]);
        } catch (Throwable $e) {
            Log::error('Stripe recipient onboarding failed from API.', [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not start bank account setup.',
            ], 500);
        }
    }

    /**
     * Re-check the Stripe recipient account and its external bank accounts.
     * Call this when Flutter resumes after the hosted onboarding flow.
     */
    public function refreshStripePayoutStatus(Request $request)
    {
        $user = $request->user();

        if (!$user->stripe_account_id) {
            return response()->json([
                'payouts_ready' => false,
                'stripe_transfers_status' => null,
                'stripe_bank_accounts' => [],
                'message' => 'No Stripe recipient account has been created.',
            ]);
        }

        try {
            $stripe = $this->stripe();

            $account = $stripe->v2->core->accounts->retrieve(
                $user->stripe_account_id,
                ['include' => ['configuration.recipient']]
            );

            $status = data_get(
                $account,
                'configuration.recipient.capabilities.stripe_balance.stripe_transfers.status'
            );

            $externalAccounts = $stripe->accounts->allExternalAccounts(
                $user->stripe_account_id,
                ['object' => 'bank_account', 'limit' => 10]
            );

            $bankAccounts = collect($externalAccounts->data)->map(fn ($external) => [
                'id' => $external->id,
                'bank_name' => $external->bank_name ?: 'Bank account',
                'last4' => $external->last4,
                'currency' => strtoupper($external->currency),
                'default_for_currency' => (bool) $external->default_for_currency,
            ])->values();

            $payoutsReady = $status === 'active' && $bankAccounts->isNotEmpty();

            return response()->json([
                'payouts_ready' => $payoutsReady,
                'stripe_transfers_status' => $status,
                'stripe_bank_accounts' => $bankAccounts,
                'message' => $payoutsReady
                    ? 'Bank account connected and withdrawals are enabled.'
                    : 'Stripe onboarding is not complete yet.',
            ]);
        } catch (Throwable $e) {
            Log::error('Stripe payout status refresh failed.', [
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to verify Stripe payout status.',
            ], 500);
        }
    }

    /**
     * Start Stripe Identity document verification.
     */
    public function startIdentityVerification(Request $request)
    {
        $user = $request->user();

        try {
            $session = $this->stripe()->identity->verificationSessions->create([
                'type' => 'document',
                'client_reference_id' => (string) $user->id,
                'provided_details' => [
                    'email' => $user->email,
                    'phone' => $user->phone ?: null,
                ],
                'options' => [
                    'document' => ['require_matching_selfie' => true],
                ],
                'metadata' => ['saturn_user_id' => (string) $user->id],
                'return_url' => route('account.index'),
            ]);

            $user->stripe_identity_session_id = $session->id;
            $user->save();

            return response()->json([
                'message' => 'Identity verification session created.',
                'url' => $session->url,
                'session_id' => $session->id,
            ]);
        } catch (Throwable $e) {
            Log::error('Stripe Identity session creation failed from API.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not start identity verification.',
            ], 500);
        }
    }

    public function enableTwoFactor(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json([
                'message' => 'Two-factor setup has not been started.',
            ], 422);
        }

        if ($user->two_factor_confirmed_at) {
            return response()->json([
                'message' => 'Two-factor authentication is already enabled.',
            ], 409);
        }

        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $validated['code']
        );

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid authentication code.',
            ], 422);
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => \Illuminate\Support\Str::random(10) . '-' . \Illuminate\Support\Str::random(10))
            ->all();

        $user->two_factor_confirmed_at = now();
        $user->two_factor_recovery_codes = encrypt(json_encode($recoveryCodes));
        $user->save();

        // Recovery codes are returned only once. Do not expose them in index().
        return response()->json([
            'message' => 'Two-factor authentication has been enabled.',
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    public function disableTwoFactor(Request $request)
    {
        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return response()->json([
            'message' => 'Two-factor authentication has been disabled.',
        ]);
    }

    public function storeBank(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'iban' => ['required', 'string', 'max:34'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $user = $request->user();
        $isFirst = !$user->bankAccounts()->exists();

        $bank = $user->bankAccounts()->create([
            'bank_name' => $validated['bank_name'],
            'iban' => strtoupper(preg_replace('/\s+/', '', $validated['iban'])),
            'currency' => strtoupper($validated['currency']),
            'is_primary' => $isFirst,
        ]);

        return response()->json([
            'message' => 'Bank account linked successfully.',
            'bank' => [
                'id' => $bank->id,
                'bank_name' => $bank->bank_name,
                'masked_iban' => $bank->masked_iban
                    ?? ('****' . substr((string) $bank->iban, -4)),
                'currency' => $bank->currency,
                'is_primary' => (bool) $bank->is_primary,
            ],
        ], 201);
    }

    /**
     * This preserves the existing local-ledger withdrawal workflow.
     * It does not initiate a Stripe payout.
     */
    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10'],
            'bank_account_id' => ['required', 'integer', 'exists:bank_accounts,id'],
        ]);

        $userId = $request->user()->id;

        $bankAccount = BankAccount::where('id', $validated['bank_account_id'])
            ->where('user_id', $userId)
            ->firstOrFail();

        $amount = (float) $validated['amount'];

        $result = DB::transaction(function () use ($userId, $amount, $bankAccount) {
            // Lock the user row to prevent concurrent requests overdrawing the balance.
            $user = \App\Models\User::whereKey($userId)->lockForUpdate()->firstOrFail();

            if ($amount > (float) $user->balance || $amount < 10) {
                return false;
            }

            $user->decrement('balance', $amount);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'status' => 'pending',
                'description' => 'Withdrawal to ' . $bankAccount->bank_name
                    . ' (' . substr((string) $bankAccount->iban, -4) . ')',
            ]);

            return true;
        });

        if (!$result) {
            return response()->json([
                'message' => 'Insufficient balance or invalid withdrawal amount.',
            ], 422);
        }

        return response()->json([
            'message' => 'Withdrawal request submitted successfully.',
        ], 202);
    }

    /**
     * The website controller currently does not persist an accreditation
     * application. Do not mark the user accredited merely because they apply.
     */
    public function requestAccreditation(Request $request)
    {
        return response()->json([
            'message' => 'Accreditation request submitted for review.',
        ], 202);
    }

    public function logoutSession(Request $request, string $id)
    {
        $currentSessionId = $request->hasSession()
            ? $request->session()->getId()
            : null;

        if ($currentSessionId !== null && hash_equals($currentSessionId, $id)) {
            return response()->json([
                'message' => 'You cannot log out the current session here.',
            ], 422);
        }

        $deleted = DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Session not found or already logged out.',
            ], 404);
        }

        return response()->json([
            'message' => 'Session logged out successfully.',
        ]);
    }

    private function stripe(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    private function stripeCountryCode(?string $country): string
    {
        $country = strtoupper(trim($country ?: 'RO'));

        $countryMap = [
            'ROMANIA' => 'RO',
            'ITALY' => 'IT',
            'FRANCE' => 'FR',
            'SPAIN' => 'ES',
        ];

        $country = $countryMap[$country] ?? $country;

        return preg_match('/^[A-Z]{2}$/', $country) ? $country : 'RO';
    }
}
