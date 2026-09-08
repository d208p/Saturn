<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'title',
        'category',
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
}