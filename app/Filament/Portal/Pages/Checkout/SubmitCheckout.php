<?php

namespace App\Filament\Portal\Pages\Checkout;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentConfirmation;
use App\Models\WalletTransaction;
use App\Models\Promo;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use App\Services\BillingService;
use Exception;

class SubmitCheckout
{
    public function execute(array $data, array $intent, $user, $appliedPromoCode)
    {
        $tenantId = $data['tenant_id'] ?? null;
        if ($tenantId) {
            $tenant = $user->tenants()->find($tenantId);
        } else {
            $tenant = $user->tenants->first();
        }

        $paymentMethod = $data['payment_method'] ?? 'bank_transfer';
        
        $price = 0;
        $paymentType = 'subscription';
        $itemType = null;
        $itemId = null;

        if (($intent['type'] ?? '') === 'subscription') {
            $plan = Plan::findOrFail($intent['plan_id']);
            $price = $plan->price;
            $paymentType = 'subscription';
            $itemType = 'plan';
            $itemId = $plan->id;
        } elseif (($intent['type'] ?? '') === 'manual_topup') {
            $price = $intent['amount'];
            $paymentType = 'wallet_topup';
            $itemType = 'topup';
            $itemId = null;
        } elseif (($intent['type'] ?? '') === 'addon') {
            $addon = \App\Models\Addon::findOrFail($intent['addon_id']);
            $price = $addon->price;
            $paymentType = 'addon';
            $itemType = 'addon';
            $itemId = $addon->id;
        }

        $adminFee = settingCentral('global_admin_fee', 0);
        $discountAmount = 0;
        $promoId = null;

        if ($appliedPromoCode) {
            $promo = Promo::where('code', $appliedPromoCode)->first();
            if ($promo) {
                $discountAmount = $promo->calculateDiscount($price);
                $promoId = $promo->id;
            }
        }

        $totalAmount = $price - $discountAmount + $adminFee;

        DB::transaction(function () use ($price, $paymentType, $itemType, $itemId, $adminFee, $discountAmount, $promoId, $totalAmount, $user, $tenant, $data, $paymentMethod) {
            
            $invoiceStatus = $paymentMethod === 'wallet' ? 'paid' : 'waiting_approval';
            
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'user_id' => $user->id,
                'subtotal' => $price,
                'admin_fee' => $adminFee,
                'promo_id' => $promoId,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => $invoiceStatus,
                'payment_channel' => $paymentMethod === 'wallet' ? 'wallet' : 'manual_transfer',
            ]);

            if ($itemType !== null) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'tenant_id' => $tenant ? $tenant->id : null,
                    'qty' => 1,
                    'price' => $price,
                ]);
            }

            if ($paymentMethod === 'bank_transfer') {
                $this->processBankTransfer($invoice, $data, $totalAmount);
            } else if ($paymentMethod === 'wallet') {
                $this->processWalletPayment($invoice, $totalAmount, $user);
            }

            if ($promoId) {
                $promo = Promo::find($promoId);
                if ($promo && $promo->usage_limit !== null) {
                    $promo->decrement('usage_limit');
                }
            }
        });
    }

    private function processBankTransfer(Invoice $invoice, array $data, $totalAmount)
    {
        $bankAccount = \App\Models\BankAccount::find($data['bank_account_id'] ?? null);
        
        $receiptPath = $data['receipt_path'] ?? null;
        if (is_array($receiptPath)) {
            $receiptPath = array_values($receiptPath)[0] ?? null;
        }

        if ($bankAccount) {
            PaymentConfirmation::create([
                'invoice_id' => $invoice->id,
                'bank_account_id' => $bankAccount->id,
                'sender_bank_name' => $data['sender_bank_name'] ?? '-',
                'sender_account_name' => $data['sender_account_name'] ?? '-',
                'transfer_amount' => $totalAmount,
                'proof_file_path' => $receiptPath,
                'status' => 'pending',
            ]);
        }
    }

    private function processWalletPayment(Invoice $invoice, $totalAmount, $user)
    {
        $wallet = $user->wallet()->firstOrCreate([]);
        // Pessimistic lock row wallet untuk mencegah race condition overdraft
        $wallet = \App\Models\Wallet::where('id', $wallet->id)->lockForUpdate()->first();

        // Idempotent: cegah pemotongan saldo berulang jika mutasi invoice ini sudah ada
        $existingTx = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('reference_type', Invoice::class)
            ->where('reference_id', $invoice->id)
            ->first();

        if ($existingTx) {
            BillingService::payInvoice($invoice);
            return;
        }

        $balanceBefore = $wallet->balance ?? 0;
        
        if ($balanceBefore < $totalAmount) {
            throw new Exception("Saldo Wallet tidak mencukupi.");
        }

        $balanceAfter = $balanceBefore - $totalAmount;
        $wallet->decrement('balance', $totalAmount);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'out',
            'category' => \App\Enums\TransactionCategory::PAYMENT,
            'amount' => $totalAmount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_id' => $invoice->id,
            'reference_type' => Invoice::class,
            'description' => 'Pembayaran Tagihan #' . $invoice->invoice_number,
        ]);

        // Aktifkan langsung menggunakan BillingService
        BillingService::payInvoice($invoice);
    }
}
