<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApiAccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('bankAccounts');

        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'dob' => optional($user->dob)->format('Y-m-d'),
                'country' => $user->country,
                'address' => $user->address,
                'created_at' => $user->created_at->format('M Y'),
                'kyc_status' => $user->kyc_status ?? 'unverified',
                'balance' => (float) ($user->balance ?? 0),
                'identity_verified' => (bool) $user->identity_verified,
                'address_verified' => (bool) $user->address_verified,
                'selfie_verified' => (bool) $user->selfie_verified,
                'accredited_investor' => (bool) $user->accredited_investor,
            ],
            'bank_accounts' => $user->bankAccounts->map(function ($bank) {
                return [
                    'id' => $bank->id,
                    'bank_name' => $bank->bank_name,
                    'masked_iban' => $bank->masked_iban ?? ('****' . substr($bank->iban, -4)),
                    'currency' => $bank->currency,
                    'is_primary' => (bool) $bank->is_primary,
                ];
            }),
            'sessions' => $sessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'user_agent' => $session->user_agent,
                    'ip_address' => $session->ip_address,
                    'last_activity' => $session->last_activity,
                    'is_current' => $session->id === request()->session()->getId(),
                ];
            }),
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'country' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return response()->json(['message' => 'Profile updated successfully.', 'user' => $user]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Password updated successfully.']);
    }

    public function storeBank(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'iban' => 'required|string|max:34',
            'currency' => 'required|string|size:3',
        ]);

        $user = $request->user();
        $isFirst = $user->bankAccounts()->count() === 0;

        $bank = $user->bankAccounts()->create([
            'bank_name' => $validated['bank_name'],
            'iban' => strtoupper(str_replace(' ', '', $validated['iban'])),
            'currency' => strtoupper($validated['currency']),
            'is_primary' => $isFirst,
        ]);

        return response()->json(['message' => 'Bank account linked successfully.', 'bank' => $bank]);
    }

    public function withdraw(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:' . $user->balance,
            'bank_account_id' => 'required|exists:bank_accounts,id',
        ]);

        $bankAccount = BankAccount::where('id', $validated['bank_account_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        DB::transaction(function () use ($user, $validated, $bankAccount) {
            $user->decrement('balance', $validated['amount']);

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $validated['amount'],
                'status' => 'pending',
                'description' => 'Withdrawal to ' . $bankAccount->bank_name . ' (' . substr($bankAccount->iban, -4) . ')',
            ]);
        });

        return response()->json(['message' => 'Withdrawal request submitted successfully.']);
    }

    public function requestAccreditation(Request $request)
    {
        $user = $request->user();
        $user->update(['accredited_investor' => true]);

        return response()->json(['message' => 'Accredited investor application submitted for review.']);
    }

    public function logoutSession(Request $request, $id)
    {
        DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Device logged out successfully.']);
    }
}