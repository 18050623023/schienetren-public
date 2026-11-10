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
        if (Schema::hasTable('purchase_orders') && !Schema::hasColumn('purchase_orders', 'attachment')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->string('attachment')->nullable()->after('notes');
            });
        }

        if (Schema::hasTable('goods_receipts') && !Schema::hasColumn('goods_receipts', 'attachment')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->string('attachment')->nullable()->after('notes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'attachment')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('attachment');
            });
        }

        if (Schema::hasTable('goods_receipts') && Schema::hasColumn('goods_receipts', 'attachment')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->dropColumn('attachment');
            });
        }
    }
};
