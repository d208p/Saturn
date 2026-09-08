<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Fetch user transactions (with optional asset relationship)
        $transactions = $user->transactions()
            ->with('asset')
            ->latest()
            ->paginate(15);

        // 2. Fetch user orders (if an Order model exists, otherwise fallback to empty collection)
        $orders = method_exists($user, 'orders') 
            ? $user->orders()->with('asset')->latest()->get() 
            : collect();

        return view('dashboard.activity', compact('transactions', 'orders'));
    }
}