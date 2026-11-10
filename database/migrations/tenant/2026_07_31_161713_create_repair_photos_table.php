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
        Schema::create('repair_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_device_id')->constrained('repair_devices')->onDelete('cascade');
            $table->enum('type', ['before', 'after','damage', 'invoice'])->default('before');
            $table->string('path');
            $table->foreignId('taken_by')->nullable()->constrained('users');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_photos');
    }
};
