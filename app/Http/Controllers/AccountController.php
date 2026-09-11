<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('bankAccounts');

        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        return view('dashboard.account', compact('user', 'sessions'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'country' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    // Bank Account Handlers
    public function storeBank(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'iban' => 'required|string|max:34',
            'currency' => 'required|string|size:3',
        ]);

        $user = Auth::user();

        // Check if user already has bank accounts
        $isFirst = $user->bankAccounts()->count() === 0;

        $user->bankAccounts()->create([
            'bank_name' => $validated['bank_name'],
            'iban' => strtoupper(str_replace(' ', '', $validated['iban'])),
            'currency' => strtoupper($validated['currency']),
            'is_primary' => $isFirst,
        ]);

        return back()->with('success', 'Bank account linked successfully.');
    }

    public function withdraw(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:' . $user->balance,
            'bank_account_id' => 'required|exists:bank_accounts,id',
        ]);

        $bankAccount = BankAccount::where('id', $validated['bank_account_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        DB::transaction(function () use ($user, $validated, $bankAccount) {
            // Deduct user balance
            $user->decrement('balance', $validated['amount']);

            // Create a pending transaction record
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $validated['amount'],
                'status' => 'pending',
                'description' => 'Withdrawal to ' . $bankAccount->bank_name . ' (' . substr($bankAccount->iban, -4) . ')',
            ]);
        });

        return back()->with('success', 'Withdrawal request submitted successfully.');
    }

    // Verification Handlers
    public function requestAccreditation()
    {
        $user = Auth::user();

        // Toggle or submit request
        $user->update([
            'accredited_investor' => true,
        ]);

        return back()->with('success', 'Accredited investor application submitted for review.');
    }

    public function logoutSession($id)
    {
        DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', 'Device logged out successfully.');
    }
}