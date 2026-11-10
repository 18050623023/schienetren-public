<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterSkillLevelsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('skill_levels')->delete();
        
        \Illuminate\Support\Facades\DB::table('skill_levels')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Senior',
                'created_at' => '2026-08-05 23:35:23',
                'updated_at' => '2026-08-05 23:35:23',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Junior',
                'created_at' => '2026-08-05 23:35:41',
                'updated_at' => '2026-08-05 23:35:41',
            ),
        ));
        
        
    }
}
