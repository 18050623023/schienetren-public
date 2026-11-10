<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['invoice_number', 'user_id', 'subtotal', 'admin_fee', 'promo_id', 'discount_amount', 'unique_code', 'total_amount', 'status', 'payment_channel', 'paid_at'];
    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    
    public function paymentConfirmation()
    {
        return $this->hasOne(PaymentConfirmation::class);
    }
}