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
        Schema::table('repair_payments', function (Blueprint $table) {
            $table->string('type', 20)->default('payment')->after('repair_invoice_id');
            $table->string('refund_reason', 255)->nullable()->after('notes');
        });

        Schema::table('repair_warranty_claims', function (Blueprint $table) {
            $table->decimal('refund_amount', 15, 2)->nullable()->after('notes');
            $table->foreignId('refund_payment_id')->nullable()->after('refund_amount')->constrained('repair_payments')->nullOnDelete();
            $table->string('return_item_action', 50)->nullable()->after('refund_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_warranty_claims', function (Blueprint $table) {
            $table->dropForeign(['refund_payment_id']);
            $table->dropColumn(['refund_amount', 'refund_payment_id', 'return_item_action']);
        });

        Schema::table('repair_payments', function (Blueprint $table) {
            $table->dropColumn(['type', 'refund_reason']);
        });
    }
};
