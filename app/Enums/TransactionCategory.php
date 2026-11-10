<?php

namespace App\Enums;

enum TransactionCategory: string
{
    case TOPUP = 'topup';
    case SUBSCRIPTION_PURCHASE = 'subscription_purchase';
    case ADDON_PURCHASE = 'addon_purchase';
    case PAYMENT = 'payment';
    case REFUND = 'refund';
    case WITHDRAWAL = 'withdrawal';
    case PROMO_BONUS = 'promo_bonus';
    case ADMIN_ADJUSTMENT = 'admin_adjustment';

    public function getLabel(): string
    {
        return match ($this) {
            self::TOPUP => 'Top Up',
            self::SUBSCRIPTION_PURCHASE => 'Pembelian Berlangganan',
            self::ADDON_PURCHASE => 'Pembelian Addon',
            self::PAYMENT => 'Pembayaran Tagihan',
            self::REFUND => 'Pengembalian Dana',
            self::WITHDRAWAL => 'Penarikan Dana',
            self::PROMO_BONUS => 'Bonus Promo',
            self::ADMIN_ADJUSTMENT => 'Penyesuaian Admin',
        };
    }
}
