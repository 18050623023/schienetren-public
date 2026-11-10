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
        Schema::create('stock_opname_item_serials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_opname_item_id')
                ->constrained()
                ->cascadeOnDelete();

            // Produk & variant untuk validasi cepat
            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();

            // Serial fisik (hasil scan atau input)
            $table->string('serial_number')->index();

            // Serial di sistem (boleh null jika EXTRA)
            $table->foreignId('product_serial_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Status serial: ok / lost / extra
            $table->enum('status', ['OK', 'LOST', 'EXTRA'])->default('OK');

            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_item_serials');
    }
};
