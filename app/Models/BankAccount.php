<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'iban',
        'currency',
        'is_primary',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Masks IBAN for secure UI display (e.g., RO49 •••• 1234)
    public function getMaskedIbanAttribute(): string
    {
        $clean = str_replace(' ', '', $this->iban);
        if (strlen($clean) <= 8) {
            return $this->iban;
        }
        return substr($clean, 0, 4) . ' •••• •••• •••• ' . substr($clean, -4);
    }
}