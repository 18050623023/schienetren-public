<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            // $table->softDeletes();
            $table->string('id')->primary();

            // your custom columns may go here
            $table->string('business_name')->nullable();
            $table->foreignId('user_id')
                  ->constrained() // Membuat foreign key ke tabel 'users'
                  ->onDelete('cascade'); // Opsional: Hapus profile jika user dihapus
            
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->integer('version')->default(1)->comment('Optimistic locking counter untuk mencegah race condition');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
