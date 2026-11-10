<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterRolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('roles')->delete();
        
        \Illuminate\Support\Facades\DB::table('roles')->insert(array (
            0 => 
            array (
                'deleted_at' => NULL,
                'id' => 1,
                'name' => 'owner',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 10:22:36',
                'updated_at' => '2026-08-03 10:22:36',
            ),
            1 => 
            array (
                'deleted_at' => NULL,
                'id' => 2,
                'name' => 'teknisi',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-05 23:33:57',
                'updated_at' => '2026-08-05 23:33:57',
            ),
            2 => 
            array (
                'deleted_at' => NULL,
                'id' => 3,
                'name' => 'customer_service',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-23 23:59:28',
                'updated_at' => '2026-08-23 23:59:28',
            ),
        ));
        
        
    }
}
