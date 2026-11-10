<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterTaxesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('taxes')->delete();
        
        
        
    }
}
