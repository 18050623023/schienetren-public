<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterSkillSpecializationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('skill_specializations')->delete();
        
        \Illuminate\Support\Facades\DB::table('skill_specializations')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Software',
                'created_at' => '2026-08-05 23:35:14',
                'updated_at' => '2026-08-05 23:35:14',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Hardware',
                'created_at' => '2026-08-05 23:35:35',
                'updated_at' => '2026-08-05 23:35:35',
            ),
        ));
        
        
    }
}
