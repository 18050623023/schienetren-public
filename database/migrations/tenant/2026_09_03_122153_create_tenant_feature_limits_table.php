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
        Schema::create('tenant_feature_limits', function (Blueprint $table) {
            $table->id();
            $table->string('feature_code', 50)->unique();
            $table->string('feature_type', 50); // limit, boolean
            $table->string('max_value', 50)->nullable()->comment('Batas kuota/hak akses yang di-push dari Central DB');
            $table->boolean('is_expired')->default(false);
            $table->timestamp('synced_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_feature_limits');
    }
};
