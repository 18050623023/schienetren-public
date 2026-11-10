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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

            // $table->enum('type', ['in', 'out', 'transfer_in', 'transfer_out', 'adjust']);
            $table->enum('direction', ['in', 'out', 'adjust']);

            // $table->enum('type', [
            //     'initial','audit','purchase','sale','transfer','adjustment'
            // ]);

            $table->integer('quantity');
            $table->text('note')->nullable();
            // referensi barang masuk / keluar
            $table->morphs('source');
            // $table->enum('source_type', ['purchase', 'sale', 'transfer','repair', 'audit']);
            // $table->smallInteger('source_id');
            $table->timestamps();
        });

        Schema::create('stock_movement_serials', function (Blueprint $table) {
            $table->foreignId('stock_movement_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_serial_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_movement_serials');
    }
};
