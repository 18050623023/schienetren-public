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

        $tableNames = config('permission.table_names');

        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';

        // Schema::create('employee_roles', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name'); // Technician, Admin, Finance
        //     $table->timestamps();
        // });

        Schema::create('employee_role_assignments', function (Blueprint $table) use ($tableNames, $pivotRole) {
            $table->id();
            $table->foreignId('employee_assignment_id')->constrained('employee_assignments')->onDelete('cascade');

            $table->foreignId($pivotRole)
                ->constrained($tableNames['roles'])
                ->cascadeOnDelete();
            // $table->foreignId('employee_role_id')->constrained('employee_roles')->onDelete('cascade');

            $table->timestamps();

            $table->unique(['employee_assignment_id', $pivotRole]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_roles');
        Schema::dropIfExists('employee_role_assignments');
    }
};
