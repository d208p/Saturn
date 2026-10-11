<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Google2FA;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse|RedirectResponse
{
    $request->authenticate();

    $user = Auth::user();

    // Check if user has 2FA enabled
    if ($user->two_factor_confirmed_at) {
        // Temporarily store user ID for challenge verification
        session(['login.id' => $user->id]);

        // Log out immediately until 2FA code is verified
        Auth::logout();

        if ($request->wantsJson()) {
            return response()->json(['two_factor' => true], 200);
        }

        return redirect()->route('two-factor.login');
    }

    // Normal login flow without 2FA...
    if ($request->wantsJson()) {
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['user' => $user, 'token' => $token], 200);
    }

    $request->session()->regenerate();
    return redirect()->intended(route('dashboard'));
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse|RedirectResponse
    {
        // 1. Handle API/Flutter logout
        if ($request->wantsJson()) {
            // Revoke current Sanctum token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logged out successfully.'
            ], 200);
        }

        // 2. Handle Web Browser logout
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }


public function verifyTwoFactor(Request $request)
{
    $userId = session('login.id');
    if (!$userId) {
        return redirect()->route('login');
    }

    $user = \App\Models\User::findOrFail($userId);

    // 1. Check if trying to log in via Recovery Code
    if ($request->filled('recovery_code')) {
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $submittedCode = trim($request->recovery_code);

        $foundKey = array_search($submittedCode, $recoveryCodes);

        if ($foundKey === false) {
            return back()->withErrors(['recovery_code' => 'Invalid recovery code.']);
        }

        // Consume (remove) the used recovery code so it can't be reused
        unset($recoveryCodes[$foundKey]);
        $user->two_factor_recovery_codes = encrypt(json_encode(array_values($recoveryCodes)));
        $user->save();

    } else {
        // 2. Standard 6-digit TOTP validation
        $request->validate(['code' => ['required', 'digits:6']]);

        $valid = app('pragmarx.google2fa')->verifyKey(
            decrypt($user->two_factor_secret),
            $request->code
        );

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }
    }

    // Success: clear temp session and complete login
    session()->forget('login.id');
    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->intended(route('dashboard'));
}
}