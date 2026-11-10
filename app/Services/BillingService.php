<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentConfirmation;
use App\Models\WalletTransaction;
use App\Models\Subscription;
use App\Models\SubscriptionAddon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Format collection Plan atau Addon untuk komponen pricing cards / welcome page.
     */
    public static function formatPlansForCards($plans, $isAddon = false)
    {
        return $plans->map(function ($plan) use ($isAddon) {
            $featuresRelation = $isAddon ? $plan->addonFeatures : $plan->planFeatures->sortBy('sort_order');
            // $featuresRelation = $featuresRelation?;

            $featuresArray = $featuresRelation?->filter(fn($pf) => $pf->feature)
                ->mapWithKeys(function ($pf) {
                    return [
                        $pf->feature->code => [
                            'value' => $pf->value,
                            'name'  => $pf->feature->name,
                            'type'  => $pf->feature->type,
                        ]
                    ];
                })->toArray() ?? [];

            $humanDuration = \App\Services\TimeConversionService::dayToHuman($plan->duration_in_days);

            return (object) [
                'id'            => $plan->id,
                'name'          => $plan->name,
                'price'         => $plan->price,
                'duration'      => $humanDuration['value'],
                'duration_unit' => $humanDuration['unit'],
                'features'      => $featuresArray,
                'is_addon'      => $isAddon
            ];
        });
    }

    /**
     * Proses persetujuan konfirmasi pembayaran manual
     */
    public static function approvePaymentConfirmation(PaymentConfirmation $confirmation, $reviewerId, $notes = '')
    {
        DB::transaction(function () use ($confirmation, $reviewerId, $notes) {
            $confirmation->update([
                'status' => 'approved',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'notes' => $notes,
            ]);

            $invoice = $confirmation->invoice;
            if ($invoice && $invoice->status !== 'paid') {
                self::payInvoice($invoice);
            }
        });
    }

    /**
     * Proses penolakan konfirmasi pembayaran manual
     */
    public static function rejectPaymentConfirmation(PaymentConfirmation $confirmation, $reviewerId, $reason)
    {
        $confirmation->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        if ($confirmation->invoice) {
            $confirmation->invoice->update(['status' => 'unpaid']);
        }
    }

    /**
     * Bayar tagihan (invoice). Bisa dipanggil dari transfer manual (setelah di-approve) atau dari pemotongan wallet.
     */
    public static function payInvoice(Invoice $invoice)
    {
        // Kunci invoice dan pastikan belum berstatus paid untuk mencegah dobel aktivasi paket / topup
        $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->first();
        if ($lockedInvoice && $lockedInvoice->status === 'paid' && $lockedInvoice->paid_at !== null) {
            return;
        }
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        foreach ($invoice->items as $item) {
            if ($item->item_type === 'topup') {
                self::processTopup($invoice->user, $item->price * $item->qty, $invoice->id);
            } elseif ($item->item_type === 'plan') {
                self::activatePlan($invoice->user, $item->item_id);
            } elseif ($item->item_type === 'addon') {
                self::activateAddon($invoice->user, $item->tenant_id, $item->item_id, $item->qty);
            }
        }
    }

    private static function processTopup($user, $amount, $invoiceId)
    {
        $wallet = $user->wallet()->firstOrCreate([]);
        $wallet = \App\Models\Wallet::where('id', $wallet->id)->lockForUpdate()->first();

        // Idempotent: cegah penambahan saldo berulang untuk invoice yang sama
        $existingTx = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('reference_type', Invoice::class)
            ->where('reference_id', $invoiceId)
            ->first();

        if ($existingTx) {
            return;
        }
        $balanceBefore = $wallet->balance ?? 0;
        $balanceAfter = $balanceBefore + $amount;

        $wallet->increment('balance', $amount);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'in',
            'category' => \App\Enums\TransactionCategory::TOPUP,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_id' => $invoiceId,
            'reference_type' => Invoice::class,
            'description' => 'Top Up Saldo via Invoice #' . $invoiceId,
        ]);
    }

    private static function processRefund($user, $amount, $subscriptionId, $description = 'Refund')
    {
        if ($amount <= 0) return;

        $wallet = $user->wallet()->firstOrCreate([]);
        $balanceBefore = $wallet->balance ?? 0;
        $balanceAfter = $balanceBefore + $amount;

        $wallet->increment('balance', $amount);

        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'in',
            'category' => \App\Enums\TransactionCategory::REFUND,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'reference_id' => $subscriptionId,
            'reference_type' => Subscription::class,
            'description' => $description,
        ]);
    }

    private static function activatePlan($user, $planId)
    {
        $plan = \App\Models\Plan::find($planId);
        if (!$plan) return;

        $activeSub = $user->getActiveSubscription();

        if (!$activeSub) {
            // Tidak ada langganan aktif, langsung buat baru
            $startsAt = now();
            $endsAt = (clone $startsAt)->addDays($plan->duration_in_days);
            $graceEndsAt = (clone $endsAt)->addDays($plan->grace_period_days);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'price' => $plan->price,
                'status' => 'active',
                'auto_renew' => false,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'grace_period_ends_at' => $graceEndsAt,
            ]);
        } else {
            // Ada langganan aktif, periksa apakah Upgrade atau Downgrade/Sama
            $oldPlan = $activeSub->plan;

            if ($plan->price > $oldPlan->price) {
                // UPGRADE: Langsung ganti hari ini
                $activeSub->update(['status' => 'expired']);

                // Cancel scheduled subscriptions and refund
                $scheduledSubs = $user->subscriptions()->where('status', 'scheduled')->get();
                foreach ($scheduledSubs as $schedSub) {
                    $schedSub->update(['status' => 'cancelled']);
                    self::processRefund($user, $schedSub->price, $schedSub->id, 'Refund pembatalan otomatis paket ' . ($schedSub->plan->name ?? ''));
                }

                $startsAt = now();
                $endsAt = (clone $startsAt)->addDays($plan->duration_in_days);
                $graceEndsAt = (clone $endsAt)->addDays($plan->grace_period_days);

                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'status' => 'active',
                    'auto_renew' => false,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'grace_period_ends_at' => $graceEndsAt,
                ]);
            } else {
                // DOWNGRADE / SAMA: Antre (Scheduled)
                // Cari end date paling terakhir agar aman jika antrean bertumpuk
                $latestSub = $user->subscriptions()->whereIn('status', ['active', 'scheduled'])->orderBy('ends_at', 'desc')->first();
                $startsAt = $latestSub ? clone $latestSub->ends_at : now();

                $endsAt = (clone $startsAt)->addDays($plan->duration_in_days);
                $graceEndsAt = (clone $endsAt)->addDays($plan->grace_period_days);

                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'status' => 'scheduled',
                    'auto_renew' => false,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'grace_period_ends_at' => $graceEndsAt,
                ]);
            }
        }

        if ($subscription->status === 'active') {
            // Jika ini upgrade atau langganan baru (langsung aktif)
            // Pastikan semua tenant milik user mengarah ke subscription baru ini
            $user->tenants()->update(['subscription_id' => $subscription->id]);

            // Lalu sinkronisasi
            TenantManagerService::syncSubscriptionFeatures($subscription);
        }
    }

    private static function activateAddon($user, $tenantId, $addonId, $qty = 1)
    {
        $addon = \App\Models\Addon::find($addonId);
        $subscription = $user->subscriptions()->where('status', 'active')->first();

        if (!$addon || !$subscription) return;

        $startsAt = now();
        $endsAt = (clone $startsAt)->addDays($addon->duration_in_days);
        $graceEndsAt = (clone $endsAt)->addDays($addon->grace_period_days);

        SubscriptionAddon::create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $tenantId,
            'addon_id' => $addon->id,
            'qty' => $qty,
            'price' => $addon->price,
            'auto_renew' => false,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'grace_period_ends_at' => $graceEndsAt,
        ]);

        // Sync features to tenant DB
        TenantManagerService::syncSubscriptionFeatures($subscription);
    }
}
