<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Holding;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ApiAssetController extends Controller
{
    public function show(Request $request, Asset $asset)
    {
        $user = $request->user();

        // Watchlist
        $isWatched = $asset->watchlists()
            ->where('user_id', $user->id)
            ->exists();

        // User holding
        $holding = Holding::where('user_id', $user->id)
            ->where('asset_id', $asset->id)
            ->first();

        // Position calculations
        $unitsOwned = (float) ($holding?->shares_owned ?? 0);
        $totalInvested = (float) ($holding?->total_invested ?? 0);
        $sharePrice = (float) $asset->share_price;

        $currentValue = $unitsOwned * $sharePrice;

        $avgPurchasePrice = $unitsOwned > 0
            ? $totalInvested / $unitsOwned
            : 0;

        $unrealizedGain = $currentValue - $totalInvested;

        $gainPct = $totalInvested > 0
            ? ($unrealizedGain / $totalInvested) * 100
            : null;

        // Funding calculations
        $totalShares = (int) ($asset->total_shares ?? 0);
        $availableShares = (int) ($asset->available_shares ?? 0);

        $soldShares = max(0, $totalShares - $availableShares);

        $fundingPercentage = $totalShares > 0
            ? ($soldShares / $totalShares) * 100
            : 0;

        $canBuy = $asset->status === 'active' && $availableShares > 0;

        // Normalize asset gallery
        $gallery = $asset->images ?? [];

        if (is_string($gallery)) {
            $decoded = json_decode($gallery, true);
            $gallery = is_array($decoded) ? $decoded : [$gallery];
        }

        if (!is_array($gallery)) {
            $gallery = [];
        }

        if (!empty($asset->image)) {
            array_unshift($gallery, $asset->image);
        }

        $images = collect($gallery)
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->map(function ($path) {
                $path = trim($path);

                if (preg_match('~^https?://~i', $path)) {
                    return $path;
                }

                if (str_starts_with($path, '/')) {
                    return $path;
                }

                if (str_starts_with($path, 'storage/')) {
                    return asset($path);
                }

                return asset('storage/' . ltrim($path, '/'));
            })
            ->unique()
            ->values()
            ->all();

        // Latest 10 completed distributions
        $distributions = Transaction::where('asset_id', $asset->id)
            ->where('type', 'distribution')
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($dist) => [
                'id' => $dist->id,
                'date' => $dist->created_at?->format('M d, Y'),
                'type' => 'Quarterly distribution',
                'amount' => (float) $dist->amount,
                'amount_per_share' => (float) ($dist->amount_per_share ?? 0),
            ])
            ->values();

        // Active buyout proposal
        $activeBuyout = $asset->buyoutProposals()
            ->whereIn('status', ['voting', 'approved_pending_payout'])
            ->latest()
            ->first();

        $buyout = null;

        if ($activeBuyout) {
    // Use the same methods as the website.
    $yesVotes = (float) $activeBuyout->totalYesVotes();
    $noVotes = (float) $activeBuyout->totalNoVotes();

    // Match the website: votes as a percentage of total asset shares.
    $yesPercent = $totalShares > 0
        ? round(($yesVotes / $totalShares) * 100, 1)
        : 0;

    $votingEndsAt = $activeBuyout->voting_ends_at;

    $votingOpen = $activeBuyout->status === 'voting'
        && $votingEndsAt !== null
        && now()->lt($votingEndsAt);

    // Get the authenticated user's vote.
    $userVoteRecord = $activeBuyout->votes()
        ->where('user_id', $user->id)
        ->first();

    $buyout = [
        'id' => $activeBuyout->id,
        'status' => $activeBuyout->status,
        'offered_amount' => (float) $activeBuyout->offered_amount,
        'buyer_name' => $activeBuyout->buyer_name ?? 'A buyer',
        'voting_ends_at' => $votingEndsAt,
        'voting_open' => $votingOpen,
        'yes_votes' => $yesVotes,
        'no_votes' => $noVotes,
        'yes_percent' => $yesPercent,
        'required_percent' => (float) ($activeBuyout->required_percent ?? 66),
        'user_vote' => $userVoteRecord?->vote,
    ];
}

        return response()->json([
            'asset' => [
                'id' => $asset->id,
                'title' => $asset->title,
                'location' => $asset->location ?? 'Global',
                'category' => $asset->category ?? 'Real Estate',
                'description' => $asset->description ?? '',
                'share_price' => $sharePrice,
                'total_valuation' => (float) ($asset->total_valuation ?? 0),
                'image' => $images[0] ?? null,
                'images' => $images,
                'total_shares' => $totalShares,
                'sold_shares' => $soldShares,
                'funding_percentage' => $fundingPercentage,
                'status' => $asset->status,
                'can_buy' => $canBuy,
                'target_yield' => (float) ($asset->target_yield ?? 0),
                'total_return' => (float) ($asset->total_return ?? 0),
                'revenue_ttm' => (float) ($asset->revenue_ttm ?? 0),
                'occupancy_rate' => (float) ($asset->occupancy_rate ?? 0),
                'noi' => (float) ($asset->noi ?? 0),
                'available_shares' => $availableShares,
                'is_favorited' => $isWatched,
            ],

            'holding' => [
                'units_owned' => $unitsOwned,
                'total_invested' => $totalInvested,
                'current_value' => $currentValue,
                'avg_purchase_price' => $avgPurchasePrice,
                'unrealized_gain' => $unrealizedGain,
                'gain_pct' => $gainPct,
            ],

            'distributions' => $distributions,
            'buyout' => $buyout,
        ]);
    }

    public function toggleWatch(Request $request, Asset $asset)
    {
        $user = $request->user();

        $watchlistEntry = $asset->watchlists()
            ->where('user_id', $user->id)
            ->first();

        if ($watchlistEntry) {
            $watchlistEntry->delete();
            $status = 'removed';
        } else {
            $asset->watchlists()->create([
                'user_id' => $user->id,
            ]);

            $status = 'added';
        }

        return response()->json([
            'status' => $status,
            'is_favorited' => $status === 'added',
        ]);
    }
}
