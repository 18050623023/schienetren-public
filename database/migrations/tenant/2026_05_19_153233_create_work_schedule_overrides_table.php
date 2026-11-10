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
        Schema::create('work_schedule_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_assignment_id')->constrained('employee_assignments')->onDelete('cascade');
            $table->foreignId('work_shift_id')->constrained('work_shifts')->onDelete('cascade');
            $table->date('date');
            $table->boolean('is_working_days')->default(false);
            $table->text('reason')->nullable();

            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('type', ['custom_shift', 'force_working_day', 'force_off_day'])->default('custom_shift');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->unique(['employee_assignment_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedule_overrides');
    }
};
