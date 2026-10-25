<?php

namespace App\Http\Controllers;

use App\Models\SellOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Fetch completed/historical financial transactions
        $transactions = $user->transactions()
            ->with('asset')
            ->latest()
            ->paginate(15);

        // 2. Fetch user's sell orders (listings) from the sell_orders table
        $orders = SellOrder::where('user_id', $user->id)
            ->with('asset')
            ->latest()
            ->get();

        return view('dashboard.activity', compact('transactions', 'orders'));
    }
}