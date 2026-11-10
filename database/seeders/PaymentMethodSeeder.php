<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_methods')->upsert([
            [
                'id' => 1,
                'name' => 'Bank BCA',
                'code' => 'bca_transfer',
                'type' => 'bank_transfer',
                'account_name' => 'ADMIN1',
                'account_number' => '2141333347',
                'instructions' => null,
                'is_active' => 1,
                'created_at' => '2026-08-29 16:43:53',
                'updated_at' => '2026-08-29 16:43:53',
            ],
            [
                'id' => 2,
                'name' => 'Bank Mandiri',
                'code' => 'mandiri_transfer',
                'type' => 'bank_transfer',
                'account_name' => 'ADMIN1',
                'account_number' => '123456789',
                'instructions' => null,
                'is_active' => 1,
                'created_at' => '2026-08-29 16:44:41',
                'updated_at' => '2026-08-29 16:44:41',
            ]
        ], ['id'], ['name', 'code', 'type', 'account_name', 'account_number', 'instructions', 'is_active', 'updated_at']);
    }
}
