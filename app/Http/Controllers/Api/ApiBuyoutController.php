<?php

namespace App\Http\Controllers\Api; // Or your API namespace

use App\Http\Controllers\Controller;
use App\Models\BuyoutProposal;
use App\Models\BuyoutVote;
use App\Models\Holding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiBuyoutController extends Controller
{
    public function vote(Request $request, BuyoutProposal $proposal)
    {
        $validated = $request->validate([
            'vote' => ['required', 'in:yes,no'],
        ]);

        $user = $request->user();

        if ($proposal->status !== 'voting') {
            return response()->json([
                'message' => 'Voting is no longer active for this proposal.',
            ], 422);
        }

        if (now()->greaterThan($proposal->voting_ends_at)) {
            return response()->json([
                'message' => 'The voting period has expired.',
            ], 422);
        }

        $holding = Holding::where('user_id', $user->id)
            ->where('asset_id', $proposal->asset_id)
            ->first();

        if (!$holding || $holding->shares_owned < 1) {
            return response()->json([
                'message' => 'You must own at least 1 share to vote.',
            ], 422);
        }

        DB::transaction(function () use ($proposal, $user, $validated, $holding) {
            // Register or update vote with current share weight
            BuyoutVote::updateOrCreate(
                [
                    'buyout_proposal_id' => $proposal->id,
                    'user_id'            => $user->id,
                ],
                [
                    'vote'   => $validated['vote'],
                    'weight' => $holding->shares_owned,
                ]
            );

            // Re-evaluate voting status instantly
            $totalShares = $proposal->asset->total_shares;
            $yesVotes = $proposal->totalYesVotes();
            $noVotes = $proposal->totalNoVotes();

            $yesPercent = $totalShares > 0 ? ($yesVotes / $totalShares) * 100 : 0;
            $noPercent = $totalShares > 0 ? ($noVotes / $totalShares) * 100 : 0;

            // Threshold Check: Passed (>66%)
            if ($yesPercent >= 66.0) {
                $proposal->update(['status' => 'approved_pending_payout']);
            } 
            // Threshold Check: Rejected (>34% NO votes make 66% YES mathematically impossible)
            elseif ($noPercent > 34.0) {
                $proposal->update(['status' => 'rejected']);
            }
        });

        return response()->json([
            'message' => 'Your vote has been cast successfully.',
        ]);
    }
}