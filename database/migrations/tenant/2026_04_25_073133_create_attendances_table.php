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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_assignment_id')->constrained('employee_assignments')->onDelete('cascade');
            $table->date('date');
            $table->integer('overtime')->default(0);

            $table->boolean('requires_schedule_review')->default(false);
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->nullable()->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');


            $table->enum('attendance_type', ['working_day', 'off_day', 'holiday', 'override','leave'])->default('working_day');
            $table->enum('status', ['present', 'absent', 'late', 'early','off'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
