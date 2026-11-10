<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'item_type',
        'item_id',
        'tenant_id',
        'qty',
        'price'
    ];



    // Relasi Polymorphic ke Item (Produk atau Paket)
    public function item()
    {
        // Parameter 'item' akan memberi tahu Laravel untuk mencari 
        // kolom 'item_type' dan 'item_id'
        return $this->morphTo('item');
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}