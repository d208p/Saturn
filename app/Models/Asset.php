<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'title',
        'category',
        'country',
        'image',
        'description',
        'total_valuation',
        'total_shares',
        'available_shares',
        'share_price',
        'status',
    ];

    public function holdings()
    {
        return $this->hasMany(Holding::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function sellOrders(): HasMany
    {
        return $this->hasMany(SellOrder::class);
    }

    public function buyoutProposals(): HasMany
    {
        return $this->hasMany(BuyoutProposal::class);
    }
}