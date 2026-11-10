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
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedInteger('sequence'); //Helper untuk nomor reparasi berdasarkan no urut per branch
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->string('status'); // enum helper class
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('canceled_at')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->foreignId('canceled_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();

            $table->index('branch_id');
            $table->index('client_id');
            $table->index('status');
            $table->index('created_by');
            $table->index('received_by');
            $table->index('approved_by');
            $table->index('created_at');

            $table->unique(['branch_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
