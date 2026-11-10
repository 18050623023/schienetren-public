<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterPaymentMethodsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('payment_methods')->delete();
        
        \Illuminate\Support\Facades\DB::table('payment_methods')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Mandiri',
                'type' => 'transfer',
                'is_active' => 1,
                'created_at' => '2026-08-06 17:17:31',
                'updated_at' => '2026-08-06 17:17:31',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Shopee Pay',
                'type' => 'ewallet',
                'is_active' => 1,
                'created_at' => '2026-08-06 17:17:42',
                'updated_at' => '2026-08-06 17:17:42',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Cash ',
                'type' => 'cash',
                'is_active' => 1,
                'created_at' => '2026-08-06 17:17:58',
                'updated_at' => '2026-08-06 17:17:58',
            ),
        ));
        
        
    }
}
