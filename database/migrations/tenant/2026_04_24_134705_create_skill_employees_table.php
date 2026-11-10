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
        Schema::create('skill_specializations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); //Hardware, Software, HP
            $table->timestamps();
        });

        Schema::create('skill_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); //Beginner, Intermediate, Advanced
            $table->timestamps();
        });

        Schema::create('skill_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('skill_specialization_id')->constrained('skill_specializations')->onDelete('cascade');
            $table->foreignId('skill_level_id')->constrained('skill_levels')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_specializations');
        Schema::dropIfExists('skill_levels');
        Schema::dropIfExists('skill_employees');
    }
};
