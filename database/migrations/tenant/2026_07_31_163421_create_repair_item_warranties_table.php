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
        Schema::create('repair_item_warranties', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('repair_item_id')->constrained('repair_items')->onDelete('cascade');

            //diambil dari waranties di product
            $table->unsignedSmallInteger('duration_value')->nullable();
            $table->enum('duration_unit', ['day', 'month', 'year'])->nullable();
            
            // diisi berdasarkan tanggal barang di terima, lalu ditambahkan durasi
            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->enum('status', ['active', 'expired','void'])->default('active');
            $table->text('notes')->nullable();
            
            $table->timestamps();

            $table->index('end_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_item_warranties');
    }
};
