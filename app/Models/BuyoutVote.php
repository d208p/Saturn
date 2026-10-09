<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyoutVote extends Model
{
    protected $fillable = [
        'buyout_proposal_id',
        'user_id',
        'vote',
        'weight',
    ];

    public function proposal()
    {
        return $this->belongsTo(BuyoutProposal::class, 'buyout_proposal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}