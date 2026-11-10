<?php

namespace Database\Seeders;

use App\Models\TopupPackage;
use Illuminate\Database\Seeder;

class TopupPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'id' => 1,
                'name' => 'Promo Kemerdekaan ',
                'price' => '100000.00',
                'received_balance' => '110000.00',
                'is_active' => 1,
                'created_at' => '2026-09-01 02:37:49',
                'updated_at' => '2026-09-01 02:37:49',
            ]
        ];

        // foreach ($packages as $package) {
        //     TopupPackage::updateOrCreate(['id' => $package['id']], $package);
        // }
    }
}
