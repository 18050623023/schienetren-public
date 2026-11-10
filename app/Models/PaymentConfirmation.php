<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentConfirmation extends Model
{
    protected $fillable = ['invoice_id', 'bank_account_id', 'sender_bank_name', 'sender_account_name', 'transfer_amount', 'proof_file_path', 'notes', 'status', 'reviewed_by', 'reviewed_at', 'rejection_reason'];
    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}