<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    /**
     * Show a notice telling the user to check their email.
     */
    public function notice()
    {
        return view('auth.verify-email');
    }

    /**
     * Send the verification email to the authenticated user.
     */
    public function send(Request $request, EmailService $emailService)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard');
        }

        // Generate a temporary signed URL valid for 60 minutes
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $request->user()->id, 'hash' => sha1($request->user()->email)]
        );

        $emailService->sendTransactionalEmail(
            $request->user(),
            'Verify Your Saturn Account Email',
            'emails.verify-email',
            [
                'userName'        => $request->user()->name,
                'verificationUrl' => $verificationUrl,
            ]
        );

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Mark the user's email as verified when they click the link.
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->email))) {
            abort(403, 'Invalid verification link.');
        }

        if (!validator(['id' => $id, 'hash' => $hash], [])->validate() && !$request->hasValidSignature()) {
            abort(403, 'This verification link has expired.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard?verified=1');
        }

        // Update the timestamp column!
        $user->markEmailAsVerified();

        return redirect()->intended('/dashboard?verified=1');
    }
}