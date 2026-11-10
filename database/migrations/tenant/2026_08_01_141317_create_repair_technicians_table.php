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
        Schema::create('repair_technicians', function (Blueprint $table) {
            $table->id();

            $table->foreignId('repair_device_id')->constrained('repair_devices')->onDelete('cascade');
            $table->foreignId('employee_assignment_id')->constrained('employee_assignments')->onDelete('cascade');

            $table->enum('role', ['primary', 'assistant', 'qc', 'transferred'])->default('primary');

            $table->foreignId('compensation_component_id')->nullable()->constrained('compensation_components')->nullOnDelete();
            $table->integer('commission_rate')->default(0);
            $table->integer('commission_amount')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_technicians');
    }
};
