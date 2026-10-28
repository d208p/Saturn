<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show the 'Forgot Password' request form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-pass');
    }

    /**
     * Send the password reset email with token link.
     */
    public function sendResetLinkEmail(Request $request, EmailService $emailService)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We could not find a Saturn account associated with that email address.',
        ]);

        $token = Str::random(64);

        // Store or update token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);
        $user = User::where('email', $request->email)->first();

        // Send via Saturn Email Service
        $emailService->sendTransactionalEmail(
            $user,
            'Reset Your Saturn Account Password',
            'emails.reset-password',
            [
                'userName' => $user->name,
                'resetUrl' => $resetUrl,
            ]
        );

        return back()->with('status', 'We have sent a password reset link to your email address.');
    }

    /**
     * Show the form to enter a new password.
     */
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-pass', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Validate token and reset the password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email|exists:users,email',
            'password'              => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid or has expired.']);
        }

        // Optional: Token expiration check (e.g. 60 minutes)
        if (now()->subMinutes(60)->gt($record->created_at)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'This password reset link has expired.']);
        }

        // Update User Password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset successfully! You can now log in.');
    }
}