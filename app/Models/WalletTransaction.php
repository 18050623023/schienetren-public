<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;


class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id', 
        'type', 
        'category', 
        'amount', 
        'balance_before', 
        'balance_after', 
        'reference_id', 
        'reference_type', 
        'description', 
        'created_at'
    ];

    protected $casts = [
        'category' => \App\Enums\TransactionCategory::class,
    ];

    // Relasi Polymorphic ke Referensi
    public function reference()
    {
        return $this->morphTo();
    }

    // Relasi ke Wallet
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}