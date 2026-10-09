<?php

namespace App\Http\Controllers;

use App\Models\BuyoutProposal;
use App\Models\BuyoutVote;
use App\Models\Holding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BuyoutController extends Controller
{
    public function vote(Request $request, BuyoutProposal $proposal)
    {
        $request->validate([
            'vote' => 'required|in:yes,no',
        ]);

        $user = Auth::user();

        if ($proposal->status !== 'voting') {
            return back()->withErrors(['buyout' => 'Voting is no longer active for this proposal.']);
        }

        if (now()->greaterThan($proposal->voting_ends_at)) {
            return back()->withErrors(['buyout' => 'The voting period has expired.']);
        }

        $holding = Holding::where('user_id', $user->id)
            ->where('asset_id', $proposal->asset_id)
            ->first();

        if (!$holding || $holding->shares_owned < 1) {
            return back()->withErrors(['buyout' => 'You must own at least 1 share to vote.']);
        }

        DB::transaction(function () use ($proposal, $user, $request, $holding) {
            // Register or update vote with current share weight
            BuyoutVote::updateOrCreate(
                [
                    'buyout_proposal_id' => $proposal->id,
                    'user_id'            => $user->id,
                ],
                [
                    'vote'   => $request->vote,
                    'weight' => $holding->shares_owned,
                ]
            );

            // Re-evaluate voting status instantly
            $totalShares = $proposal->asset->total_shares;
            $yesVotes = $proposal->totalYesVotes();
            $noVotes = $proposal->totalNoVotes();

            $yesPercent = ($yesVotes / $totalShares) * 100;
            $noPercent = ($noVotes / $totalShares) * 100;

            // Threshold Check: Passed (>66%)
            if ($yesPercent >= 66.0) {
                $proposal->update(['status' => 'approved_pending_payout']);
            } 
            // Threshold Check: Rejected (>34% NO votes make 66% YES mathematically impossible)
            elseif ($noPercent > 34.0) {
                $proposal->update(['status' => 'rejected']);
            }
        });

        return back()->with('success', 'Your vote has been cast successfully.');
    }
}