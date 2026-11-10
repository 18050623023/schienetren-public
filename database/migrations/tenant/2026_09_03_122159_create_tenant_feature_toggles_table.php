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
        Schema::create('tenant_feature_toggles', function (Blueprint $table) {
            $table->id();
            $table->string('feature_code', 50)->unique();
            $table->boolean('is_enabled')->default(true)->comment('Sakelar On/Off modul milik client');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_feature_toggles');
    }
};
