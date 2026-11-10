<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Promo extends Model
{
    protected $fillable = [
        'code', 
        'name', 
        'target', 
        'type', 
        'value', 
        'min_spend', 
        'max_discount', 
        'start_date', 
        'end_date', 
        'usage_limit', 
        'is_active'
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Mengecek apakah promo valid untuk transaksi saat ini.
     * Mengembalikan array: [bool $isValid, string $errorMessage]
     */
    public function isValidFor(float $subtotal, string $itemType): array
    {
        if (!$this->is_active) {
            return [false, 'Promo ini sudah tidak aktif.'];
        }

        $now = Carbon::now();
        if ($this->start_date && $now->lt($this->start_date)) {
            return [false, 'Promo ini belum dimulai.'];
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return [false, 'Promo ini sudah kedaluwarsa.'];
        }

        if ($this->usage_limit !== null && $this->usage_limit <= 0) {
            return [false, 'Kuota promo ini sudah habis.'];
        }

        if ($this->target !== 'all' && $this->target !== $itemType) {
            return [false, 'Promo ini tidak berlaku untuk jenis item ini.'];
        }

        if ($subtotal < $this->min_spend) {
            return [false, 'Minimal transaksi Rp ' . number_format($this->min_spend, 0, ',', '.') . ' tidak terpenuhi.'];
        }

        return [true, ''];
    }

    /**
     * Menghitung besaran diskon berdasarkan subtotal
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'fixed') {
            // Pastikan diskon tidak lebih besar dari subtotal
            return min($this->value, $subtotal);
        }

        // Percentage
        $discountAmount = ($this->value / 100) * $subtotal;

        if ($this->max_discount > 0 && $discountAmount > $this->max_discount) {
            $discountAmount = $this->max_discount;
        }

        return min($discountAmount, $subtotal);
    }
}