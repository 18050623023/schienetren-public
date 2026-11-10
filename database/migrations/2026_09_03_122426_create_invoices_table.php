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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->foreignId('promo_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->integer('unique_code')->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['unpaid', 'waiting_approval', 'paid', 'rejected', 'cancelled', 'expired'])->default('unpaid');
            $table->enum('payment_channel', ['manual_transfer', 'wallet'])->default('manual_transfer'); // manual_transfer, wallet
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
