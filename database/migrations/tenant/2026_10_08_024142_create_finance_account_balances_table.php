<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('finance_account_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_account_id')->constrained('finance_accounts')->cascadeOnDelete();
            $table->string('period'); // e.g., '2026-10'
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['finance_account_id', 'period'], 'acc_balance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_account_balances');
    }
};
