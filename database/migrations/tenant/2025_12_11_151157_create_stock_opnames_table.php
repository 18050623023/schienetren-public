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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

            $table->string('reference')->unique(); // contoh: SO-202501-0001
            $table->date('date'); // tanggal opname
            $table->text('notes')->nullable();

            // type = jenis opname
            $table->enum('type', ['initial', 'regular'])->default('regular');

            $table->enum('status', ['in_progress', 'draft', 'posted', 'canceled'])->default('draft');

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('cenceled_by')->nullable()->constrained('users');
            $table->foreignId('posted_by')->nullable()->constrained('users');

            $table->timestamp('cenceled_at')->nullable();
            $table->timestamp('posted_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
