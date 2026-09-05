<?php

namespace App\Http\Controllers;

use App\Models\Preregistration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PreregisterController extends Controller
{
    /**
     * Show the pre-register page.
     */
    public function create(): \Illuminate\View\View
    {
        return view('preregister');
    }

    /**
     * Store a new pre-registration email.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:preregistrations,email'],
        ], [
            'email.unique' => "You're already on the list — we'll email you when we launch.",
        ]);

        Preregistration::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "You're on the list.",
            ]);
        }

        return back()->with('success', "You're on the list! We'll email you the moment Saturn opens.");
    }
}