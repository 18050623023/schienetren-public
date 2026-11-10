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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->enum('warranty_unit', ['day', 'month', 'year'])->nullable();
            $table->unsignedSmallInteger('warranty_value')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('brand_id')
                ->nullable()
                ->constrained('brands')
                ->nullOnDelete();
            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();
            $table->string('image')->nullable();
            $table->boolean('has_sn')->default(false);
            $table->boolean('has_variant')->default(false);
            $table->boolean('is_include_service')->default(false);
            $table->enum('type', ['product', 'service']);
            $table->softDeletes();
            $table->timestamps();

            $table->index('type');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
