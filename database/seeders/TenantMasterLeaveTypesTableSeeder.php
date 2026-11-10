<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterLeaveTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('leave_types')->delete();
        
        \Illuminate\Support\Facades\DB::table('leave_types')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Cuti sakit',
                'code' => 'skt',
                'default_quota' => 3,
                'quota_per_request' => 1,
                'reset_period' => 'monthly',
                'is_paid' => 1,
                'require_approval' => 1,
                'is_unlimited' => 0,
                'created_at' => '2026-08-30 16:44:44',
                'updated_at' => '2026-08-30 16:44:44',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Cuti izin',
                'code' => 'izn',
                'default_quota' => 12,
                'quota_per_request' => 3,
                'reset_period' => 'monthly',
                'is_paid' => 1,
                'require_approval' => 1,
                'is_unlimited' => 0,
                'created_at' => '2026-08-30 16:45:31',
                'updated_at' => '2026-08-30 16:45:31',
            ),
        ));
        
        
    }
}
