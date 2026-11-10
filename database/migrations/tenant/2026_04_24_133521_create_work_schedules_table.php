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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_minutes')->nullable();
            $table->integer('late_tolerance')->nullable();
            $table->integer('overtime_tolerance')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('work_schedule_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_schedule_id')->constrained('work_schedules')->onDelete('cascade');
            $table->foreignId('work_shift_id')->constrained('work_shifts')->onDelete('cascade');
            $table->integer('day_of_week')->comment('1 = Senin, 2 = Selasa, dst');
            $table->boolean('is_working_days')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
        Schema::dropIfExists('work_schedule_days');
        Schema::dropIfExists('work_schedule_shifts');
    }
};
