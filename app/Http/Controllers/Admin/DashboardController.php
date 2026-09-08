<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_assets' => Asset::count(),
            'total_volume' => Transaction::sum('amount'),
        ];

        $assets = Asset::latest()->get();

        return view('admin.admin', compact('stats', 'assets'));
    }

    public function storeAsset(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_valuation' => 'required|numeric|min:0',
            'total_shares' => 'required|integer|min:1',
            'share_price' => 'required|numeric|min:0',
        ]);

        $validated['available_shares'] = $validated['total_shares'];
        $validated['status'] = 'active';

        Asset::create($validated);

        return back()->with('success', 'New asset created successfully.');
    }
}