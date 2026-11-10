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
        Schema::create('repair_devices', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('repair_id')->constrained('repairs')->onDelete('cascade');
            $table->foreignId('repair_handover_id')->nullable()->constrained('repair_handovers')->nullOnDelete();
            $table->foreignId('device_type_id')->constrained('device_types')->onDelete('cascade');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');

            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->integer('imei')->nullable();
            $table->string('color')->nullable();

            $table->text('accessories')->nullable();
            $table->json('checklist')->nullable();

            $table->text('problems')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->nullable(); // enum helper class
            $table->string('current_stage')->default('hardware');
            $table->dateTime('estimated_finish')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('finished_at');

            $table->index('serial_number');
            $table->index('imei');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_devices');
    }
};
