<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holding extends Model
{
    protected $fillable = [
        'user_id',
        'asset_id',
        'shares_owned',
        'total_invested',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}