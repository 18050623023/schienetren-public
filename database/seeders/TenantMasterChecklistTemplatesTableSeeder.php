<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterChecklistTemplatesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('checklist_templates')->delete();
        
        \Illuminate\Support\Facades\DB::table('checklist_templates')->insert(array (
            0 => 
            array (
                'id' => 1,
                'device_type_id' => 1,
                'name' => 'LCD',
                'description' => 'asdasd',
                'is_active' => 1,
                'created_at' => '2026-08-05 17:27:43',
                'updated_at' => '2026-08-06 15:49:01',
            ),
        ));
        
        
    }
}
