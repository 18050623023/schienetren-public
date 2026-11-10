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
        Schema::create('repair_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_id')->constrained('repairs')->onDelete('cascade');
            $table->date('invoice_date');
            $table->string('invoice_number')->unique();
            $table->date('due_date')->nullable();
            $table->integer('subtotal');

            $table->foreignId('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->integer('discount_value')->default(0);

            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->integer('tax_rate')->default(0);
            $table->integer('tax_amount')->default(0);

            $table->integer('shipping_fee')->default(0);
            
            $table->integer('grand_total');
            $table->integer('paid_amount')->default(0);
            $table->integer('remaining_amount')->default(0);
            $table->enum('status', ['paid', 'unpaid', 'partially_paid'])->default('unpaid');
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_invoices');
    }
};
