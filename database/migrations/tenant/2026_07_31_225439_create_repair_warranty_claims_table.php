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
        Schema::create('repair_warranty_claims', function (Blueprint $table) {
            $table->id();

            $table->foreignId('repair_item_warranty_id')->constrained('repair_item_warranties')->onDelete('cascade');
            $table->foreignId('new_repair_id')->nullable()->constrained('repairs')->nullOnDelete();
            $table->date('claim_date');

            $table->text('problems')->nullable();
            $table->text('solution')->nullable();

            $table->enum('status', ['pending', 'in_progress', 'completed', 'rejected'])->default('pending');

            $table->foreignId('handled_by')->nullable()->constrained('users');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_warranty_claims');
    }
};
