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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->string('code');
            $table->bigInteger('discount')->default(0);
            $table->bigInteger('paid_amount')->default(0);
            $table->bigInteger('remaining_amount')->default(0);

            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('canceled_by')->nullable()->constrained('users');
            $table->foreignId('confirmed_by')->nullable()->constrained('users');

            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            $table->bigInteger('total');
            $table->enum('payment_method', ['cash', 'transfer', 'credit'])->default('cash');
            $table->date('date');
            $table->enum('status', ['draft', 'confirmed','canceled','partially', 'completed'])->default('draft');
            $table->text('notes')->nullable();
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
