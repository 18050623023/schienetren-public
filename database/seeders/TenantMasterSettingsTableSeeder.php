<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterSettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('settings')->delete();
        
        \Illuminate\Support\Facades\DB::table('settings')->insert(array (
            0 => 
            array (
                'id' => 1,
                'key' => 'tenant_bypass_roles',
                'value' => '["owner"]',
                'created_at' => '2026-08-24 19:29:34',
                'updated_at' => '2026-08-24 19:29:34',
            ),
            1 => 
            array (
                'id' => 2,
                'key' => 'invoice_prefix',
                'value' => '"INV"',
                'created_at' => '2026-08-24 19:29:34',
                'updated_at' => '2026-08-26 01:30:45',
            ),
            2 => 
            array (
                'id' => 3,
                'key' => 'color_primary',
                'value' => '"#007bff"',
                'created_at' => '2026-08-24 19:29:34',
                'updated_at' => '2026-08-24 22:29:31',
            ),
            3 => 
            array (
                'id' => 4,
                'key' => 'default_latitude',
                'value' => '-7.25750000',
                'created_at' => '2026-08-24 22:29:31',
                'updated_at' => '2026-08-24 22:35:11',
            ),
            4 => 
            array (
                'id' => 5,
                'key' => 'default_longitude',
                'value' => '112.75210000',
                'created_at' => '2026-08-24 22:29:31',
                'updated_at' => '2026-08-24 22:35:11',
            ),
            5 => 
            array (
                'id' => 6,
                'key' => 'brand_name',
                'value' => '"Schienetren"',
                'created_at' => '2026-08-26 01:29:24',
                'updated_at' => '2026-08-26 01:38:58',
            ),
            6 => 
            array (
                'id' => 7,
                'key' => 'tenant_technical_roles',
                'value' => '["teknisi"]',
                'created_at' => '2026-08-26 17:01:15',
                'updated_at' => '2026-08-26 17:01:15',
            ),
            7 => 
            array (
                'id' => 8,
                'key' => 'repair_prefix',
                'value' => '"REP"',
                'created_at' => now(),
                'updated_at' => now(),
            ),
            8 => 
            array (
                'id' => 9,
                'key' => 'po_prefix',
                'value' => '"PO"',
                'created_at' => now(),
                'updated_at' => now(),
            ),
            9 => 
            array (
                'id' => 10,
                'key' => 'goods_receipt_prefix',
                'value' => '"GR"',
                'created_at' => now(),
                'updated_at' => now(),
            ),
            10 => 
            array (
                'id' => 11,
                'key' => 'stock_transfer_prefix',
                'value' => '"TRF"',
                'created_at' => now(),
                'updated_at' => now(),
            ),
            11 => 
            array (
                'id' => 12,
                'key' => 'journal_prefix',
                'value' => '"JRN"',
                'created_at' => now(),
                'updated_at' => now(),
            ),
        ));
        
        
    }
}
