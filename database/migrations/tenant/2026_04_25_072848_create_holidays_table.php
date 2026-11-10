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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // Cakupan hari libur
            $table->enum('scope', [
                'national',
                'company',
                'branch',
            ])->default('national');

            // Pola pengulangan
            $table->enum('recurrence_type', [
                'one_time',
                'yearly',
                'monthly',
            ]);

            // Digunakan jika recurrence_type = one_time
            $table->date('date')->nullable();

            // Digunakan jika recurrence_type = yearly
            $table->unsignedTinyInteger('month')->nullable();

            // Digunakan jika recurrence_type = yearly atau monthly
            $table->unsignedTinyInteger('day')->nullable();

            // Apakah hari libur dibayar
            $table->boolean('is_paid')->default(true);
            
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('scope');
            $table->index('recurrence_type');
            $table->index('date');
            $table->index(['month', 'day']);
        });

        Schema::create('holiday_branch', function (Blueprint $table) {
            $table->foreignId('holiday_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->primary([
                'holiday_id',
                'branch_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('holiday_templates');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('holiday_branch');
    }
};
