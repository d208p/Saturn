<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\BuyoutProposal;
use App\Models\Holding;
use App\Models\PlatformFee;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\EmailService;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'          => User::count(),
            'total_assets'         => Asset::count(),
            'total_volume'         => Transaction::sum('amount'),
            'total_fees_collected' => PlatformFee::sum('amount'),
            'today_fees'           => PlatformFee::whereDate('created_at', today())->sum('amount'),
            'total_user_balances'  => User::sum('balance'),
        ];

        $recentFees = PlatformFee::with(['user', 'asset'])
            ->latest()
            ->take(10)
            ->get();

        $assets = Asset::latest()->get();

        // Load active buyout proposals for admin management
        $buyoutProposals = BuyoutProposal::with('asset')
            ->latest()
            ->get();

        return view('admin.admin', compact('stats', 'assets', 'recentFees', 'buyoutProposals'));
    }

    /**
     * Create a buyout proposal (Initiated by corporate buyer).
     */
    public function createBuyoutProposal(Request $request)
    {
        $validated = $request->validate([
            'asset_id'       => 'required|exists:assets,id',
            'buyer_name'     => 'required|string|max:255',
            'offered_amount' => 'required|numeric|min:0.01',
            'voting_days'    => 'required|integer|min:1|max:30',
        ]);

        BuyoutProposal::create([
            'asset_id'       => $validated['asset_id'],
            'buyer_name'     => $validated['buyer_name'],
            'offered_amount' => $validated['offered_amount'],
            'voting_ends_at' => now()->addDays((int) $validated['voting_days']),
            'status'         => 'voting',
        ]);

        return back()->with('success', 'Corporate buyout offer listed and voting opened.');
    }

    /**
     * Execute final payout for an approved buyout.
     */
    public function approveBuyout(BuyoutProposal $proposal)
    {
        if (!in_array($proposal->status, ['approved_pending_payout', 'voting'])) {
            return back()->with('error', 'This proposal cannot be executed.');
        }

        $asset = $proposal->asset;
        $totalShares = $asset->total_shares;
        
        // Ensure 66% threshold condition is met
        $yesPercent = ($proposal->totalYesVotes() / $totalShares) * 100;
        if ($yesPercent < 66.0 && now()->lessThan($proposal->voting_ends_at)) {
            return back()->with('error', 'Buyout threshold (66%) has not been reached yet.');
        }

        $grossAmount = (float) $proposal->offered_amount;
        $platformFeeAmount = $grossAmount * 0.01; // 1% platform fee
        $netPayoutPool = $grossAmount - $platformFeeAmount;

        DB::transaction(function () use ($proposal, $asset, $totalShares, $grossAmount, $platformFeeAmount, $netPayoutPool) {
            // 1. Log 1% Platform Fee
            PlatformFee::create([
                'user_id'        => auth()->id(),
                'asset_id'       => $asset->id,
                'source'         => 'buyout_fee',
                'fee_percentage' => 1.0,
                'amount'         => $platformFeeAmount,
            ]);

            // 2. Distribute net proceeds proportionally to shareholders
            $holdings = Holding::where('asset_id', $asset->id)->where('shares_owned', '>', 0)->get();

            foreach ($holdings as $holding) {
                $userShareRatio = $holding->shares_owned / $totalShares;
                $userPayout = $netPayoutPool * $userShareRatio;

                $user = User::find($holding->user_id);
                if ($user) {
                    $user->increment('balance', $userPayout);

                    Transaction::create([
                        'user_id'  => $user->id,
                        'asset_id' => $asset->id,
                        'type'     => 'distribution',
                        'amount'   => $userPayout,
                        'shares'   => $holding->shares_owned,
                        'status'   => 'completed',
                    ]);
                }

                // Clear holding record
                $holding->delete();
            }

            // 3. Mark asset as closed & proposal as completed
            $asset->update([
                'status'           => 'closed',
                'available_shares' => 0,
            ]);

            $proposal->update(['status' => 'completed']);
        });

        return back()->with('success', "Buyout executed. Net proceeds of €" . number_format($netPayoutPool, 2) . " distributed to shareholders (1% fee logged).");
    }

    public function distributeProfit(Request $request, EmailService $emailService)
    {
        $request->validate([
            'asset_id'     => 'required|exists:assets,id',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        $asset = Asset::findOrFail($request->asset_id);
        $grossProfit = (float) $request->total_amount;

        // Calculate circulating shares (Total shares minus currently available unsold shares)
        $circulatingShares = $asset->total_shares - $asset->available_shares;

        if ($circulatingShares <= 0) {
            return back()->with('error', 'Distribution failed: No users currently own shares of this asset.');
        }

        $taxPercentage = 12.10;

        // Calculate per-share metrics to ensure exact proportional distribution and tax splitting
        $grossPerShare = $grossProfit / $circulatingShares;
        $taxPerShare = $grossPerShare * ($taxPercentage / 100);
        $netPerShare = $grossPerShare - $taxPerShare;

        $holdings = Holding::where('asset_id', $asset->id)
            ->where('shares_owned', '>', 0)
            ->get();

        DB::transaction(function () use ($holdings, $netPerShare, $taxPerShare, $asset, $taxPercentage, $emailService) {
            $totalNetDistributed = 0;
            $totalTaxCollected = 0;

            foreach ($holdings as $holding) {
                $user = User::find($holding->user_id);
                if (!$user) continue;

                // Calculate user-specific amounts based on their owned shares
                $userNetProfit = $holding->shares_owned * $netPerShare;
                $userTaxAmount = $holding->shares_owned * $taxPerShare;

                $totalNetDistributed += $userNetProfit;
                $totalTaxCollected += $userTaxAmount;

                // 1. Credit user's balance with their share of the NET profit
                $user->increment('balance', $userNetProfit);

                // 2. Log the income transaction for the user
                Transaction::create([
                    'user_id'  => $user->id,
                    'asset_id' => $asset->id,
                    'type'     => 'income',
                    'amount'   => $userNetProfit,
                    'shares'   => $holding->shares_owned,
                    'status'   => 'completed',
                ]);

                // 3. Record an individual platform fee entry for THIS user's holding tax portion
                \App\Models\PlatformFee::create([
                    'user_id'        => $user->id,
                    'asset_id'       => $asset->id,
                    'source'         => 'management_tax',
                    'amount'         => $userTaxAmount,
                    'fee_percentage' => $taxPercentage,
                ]);

                // 4. Send an email notification to each owner individually
                $emailService->sendTransactionalEmail(
                    $user,
                    'Profit Distribution Received for ' . $asset->title,
                    'emails.profit-distribution',
                    [
                        'userName'  => $user->name,
                        'assetName' => $asset->title,
                        'amount'    => $userNetProfit,
                        'shares'    => $holding->shares_owned,
                        'taxAmount' => $userTaxAmount
                    ]
                );
            }
        });

        return back()->with('success', "Successfully distributed profits across shareholders of {$asset->title} with individual management tax records created and notification emails sent.");
    }

    public function storeAsset(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'category'        => 'required|string|max:255',
            'country'         => 'required|string|max:255',
            'description'     => 'nullable|string',
            'total_valuation' => 'required|numeric|min:0',
            'total_shares'    => 'required|integer|min:1',
            'share_price'     => 'required|numeric|min:0',
            'image'           => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $validated['image_path'] = $request->file('image')->store('assets', 'public');
        unset($validated['image']);

        $validated['available_shares'] = $validated['total_shares'];
        $validated['status'] = 'active';

        Asset::create($validated);

        return back()->with('success', 'New asset created successfully.');
    }

    /**
     * Terminate an active asset, refund shareholders (investment + 4.9% primary fee),
     * remove associated platform fee records, and mark the asset as exited.
     */
    public function endTerm(Asset $asset)
    {
        if ($asset->status !== 'active') {
            return back()->with('error', 'Only active assets can be terminated.');
        }

        DB::transaction(function () use ($asset) {
            // 1. Retrieve all active holdings for this asset
            $holdings = Holding::where('asset_id', $asset->id)
                ->where('shares_owned', '>', 0)
                ->get();

            foreach ($holdings as $holding) {
                // Base invested amount for current shares owned
                $baseAmount = $holding->shares_owned * $asset->share_price;

                // Calculate 4.9% primary market fee refund (e.g., €10.00 base -> €0.49 fee -> €10.49 total)
                $feeRefund = $baseAmount * 0.049;
                $totalRefund = $baseAmount + $feeRefund;

                $user = User::find($holding->user_id);
                if ($user) {
                    // Credit full amount + fee back to the user's cash balance
                    $user->increment('balance', $totalRefund);

                    // Record the full refund transaction log
                    Transaction::create([
                        'user_id'  => $user->id,
                        'asset_id' => $asset->id,
                        'type'     => 'distribution',
                        'amount'   => $totalRefund,
                        'shares'   => $holding->shares_owned,
                        'status'   => 'completed',
                    ]);
                }

                // Delete user's holding record
                $holding->delete();
            }

            // 2. Remove all logged platform fee entries for this asset from the database ledger
            PlatformFee::where('asset_id', $asset->id)->delete();

            // 3. Update the asset status to 'closed' and clear remaining available shares
            $asset->update([
                'status'           => 'closed',
                'available_shares' => 0,
            ]);
        });

        return back()->with('success', "Terminated asset '{$asset->title}'. All shareholders have been refunded their principal plus the 4.9% primary fee, and fee records have been purged.");
    }
}