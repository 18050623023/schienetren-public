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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->foreignId('employee_assignment_id')->constrained('employee_assignments')->onDelete('cascade');
            $table->dateTime('logged_at')->default(now());
            $table->string('photo')->nullable();

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('accuracy')->nullable();
            $table->integer('distance')->nullable();

            $table->integer('late_minutes')->nullable()->default(0);
            $table->integer('early_minutes')->nullable()->default(0);

            $table->enum('type', ['in', 'out'])->default('in');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
