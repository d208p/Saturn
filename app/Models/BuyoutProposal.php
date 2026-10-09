<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyoutProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'buyer_name',
        'offered_amount',
        'voting_ends_at',
        'status',
    ];

    protected $casts = [
        'voting_ends_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function votes()
    {
        return $this->hasMany(BuyoutVote::class);
    }

    public function totalYesVotes(): int
    {
        return (int) $this->votes()->where('vote', 'yes')->sum('weight');
    }

    public function totalNoVotes(): int
    {
        return (int) $this->votes()->where('vote', 'no')->sum('weight');
    }
}