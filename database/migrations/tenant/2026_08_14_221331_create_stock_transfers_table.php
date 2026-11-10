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

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Contoh: TRF-202608-0001');

            // Relasi Cabang
            $table->foreignId('from_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('to_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->date('date');
            $table->enum('status',['draft','in_transit','received','partial_received','canceled'])->default('draft');
            $table->text('notes')->nullable();

            // Tracking Pengguna (User)
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shipped_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('canceled_by')->nullable()->constrained('users')->nullOnDelete();

            // Waktu Aksi
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Tabel Item Detail Stock Transfer
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_transfer_id')
                ->constrained('stock_transfers')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->nullOnDelete();

            $table->integer('qty_sent')->default(0);
            $table->integer('qty_received')->default(0);
            $table->timestamps();
        });

        // 3. Tabel Detail Nomor Seri (Serial Numbers)
        Schema::create('stock_transfer_item_serials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_transfer_item_id')
                ->constrained('stock_transfer_items')
                ->cascadeOnDelete();

            $table->foreignId('product_serial_id')
                ->constrained('product_serials')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_item_serials');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
    }
};
