<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// use App\Models\UserSubscription;

class AutoRenewSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:auto-renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-renew expiring subscriptions using tenant wallet balance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-renewal process...');

        $expiringSubscriptions = \App\Models\Subscription::with('user', 'plan')
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('ends_at', '<=', now()->addDay())
            ->get();

        foreach ($expiringSubscriptions as $subscription) {
            $user = $subscription->user;
            $plan = $subscription->plan;
            $price = $subscription->price; // Locked price at purchase

            $wallet = $user->wallet;
            if ($wallet && $wallet->balance >= $price) {
                $balanceBefore = $wallet->balance;
                $balanceAfter = $balanceBefore - $price;
                
                $wallet->decrement('balance', $price);
                
                \App\Models\WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'out',
                    'category' => \App\Enums\TransactionCategory::SUBSCRIPTION_PURCHASE,
                    'amount' => $price,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => 'Auto-renewal: ' . $plan->name,
                ]);

                // Akumulasi ends_at
                $subscription->update([
                    'ends_at' => (clone $subscription->ends_at)->addDays($plan->duration_in_days),
                    'grace_period_ends_at' => (clone $subscription->ends_at)->addDays($plan->duration_in_days + $plan->grace_period_days),
                ]);

                $this->info("Renewed {$plan->name} for user {$user->name}");
            } else {
                $subscription->update([
                    'auto_renew' => false,
                ]);
                $this->warn("Insufficient balance to renew {$plan->name} for user {$user->name}");
            }
        }

        $this->info('Starting scheduled subscriptions activation process...');
        
        $scheduledSubscriptions = \App\Models\Subscription::with('user', 'plan')
            ->where('status', 'scheduled')
            ->where('starts_at', '<=', now())
            ->get();
            
        foreach ($scheduledSubscriptions as $scheduled) {
            $user = $scheduled->user;
            $activeSub = $user->getActiveSubscription();
            
            // Jika ada langganan yang masih nyangkut berstatus aktif, matikan.
            if ($activeSub && $activeSub->id !== $scheduled->id) {
               $activeSub->update(['status' => 'expired']);
            }

            $scheduled->update([
                'status' => 'active',
            ]);

            // Update subscription_id di tenant agar mengarah ke langganan baru ini
            $user->tenants()->update(['subscription_id' => $scheduled->id]);

            // Sinkronisasi fitur paket baru ini ke Tenant DB
            \App\Services\TenantManagerService::syncSubscriptionFeatures($scheduled);
            
            $this->info("Activated scheduled subscription {$scheduled->plan->name} for user {$user->name}");
        }

        $this->info('Checking for expired addons to revoke access...');
        
        // Cari addon yang baru saja kedaluwarsa dalam 48 jam terakhir
        $expiredAddons = \App\Models\SubscriptionAddon::with('subscription')
            ->where('ends_at', '<', now())
            ->where('ends_at', '>=', now()->subDays(2))
            ->get();
            
        $syncedSubs = [];
        
        foreach ($expiredAddons as $addon) {
            $sub = $addon->subscription;
            if ($sub && !in_array($sub->id, $syncedSubs)) {
                \App\Services\TenantManagerService::syncSubscriptionFeatures($sub);
                $syncedSubs[] = $sub->id;
                $this->info("Revoked expired addon access for subscription {$sub->id}");
            }
        }

        $this->info('Auto-renewal process completed.');
    }
}
