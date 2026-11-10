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
        Schema::create('compensation_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('compensation_components', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // base_salary, repair_commission
            $table->enum('type',['earning', 'deduction'])->default('earning');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('compensation_package_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compensation_package_id')->constrained('compensation_packages', 'id', 'fk_cpc_pkg_id')->onDelete('cascade');
            $table->foreignId('compensation_component_id')->constrained('compensation_components', 'id', 'fk_cpc_comp_id')->onDelete('cascade');
            $table->enum('calc_type', ['fixed', 'percentage', 'amount_per_unit', 'formula'])->default('fixed');
            $table->decimal('value', 15, 2);
            $table->text('formula')->nullable();
            $table->string('based_on', 50); //repair, pos, production //enum helper class
            $table->softDeletes();
            $table->timestamps();

            $table->unique([
                'compensation_package_id',
                'compensation_component_id'
            ], 'unq_cpc_pkg_comp');
        });

        Schema::create('compensation_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_assignment_id')->constrained('employee_assignments')->onDelete('cascade');
            $table->foreignId('compensation_package_id')->constrained('compensation_packages')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique([
                'employee_assignment_id',
                'compensation_package_id',
                'start_date'
            ], 'unq_ca_emp_pkg_date');

            $table->index([
                'start_date',
                'end_date'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compensation_packages');
        Schema::dropIfExists('compensation_components');
        Schema::dropIfExists('compensation_package_components');
        Schema::dropIfExists('compensation_assignments');
    }
};
