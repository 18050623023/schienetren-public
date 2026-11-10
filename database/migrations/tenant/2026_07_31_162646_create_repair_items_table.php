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
        Schema::create('repair_items', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('repair_device_id')->constrained('repair_devices')->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

            $table->integer('qty');
            $table->bigInteger('price');

            $table->foreignId('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
            $table->enum('discount_type', ['fixed', 'percentage'])->default('percentage');
            $table->bigInteger('discount_value')->default(0);

            $table->bigInteger('subtotal_item');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_items');
    }
};
