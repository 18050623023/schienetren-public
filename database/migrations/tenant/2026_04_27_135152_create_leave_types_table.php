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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->integer('default_quota')->nullable()->default(NULL);
            $table->integer('quota_per_request')->default(0);
            $table->enum('reset_period', ['yearly', 'monthly', 'one_time', 'none'])->default('none');
            $table->boolean('is_paid')->default(false);
            $table->boolean('require_approval')->default(true);
            $table->boolean('is_unlimited')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
