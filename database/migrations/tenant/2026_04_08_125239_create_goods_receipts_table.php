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
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            // received_by
            $table->foreignId('received_by')->constrained('users');
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->foreignId('canceled_by')->nullable()->constrained('users');

            $table->timestamp('posted_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->date('date');

            $table->enum('status', ['draft', 'posted','canceled'])->default('draft');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
