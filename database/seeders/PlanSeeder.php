<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'id' => 1,
                'type' => 'base',
                'name' => 'Basic',
                'price' => '100000.00',
                'duration' => 1,
                'duration_unit' => 'month',
                'features' => '{"branch": "true", "tenant": "1", "login_register": "true"}',
                'created_at' => '2026-09-01 02:31:16',
                'updated_at' => '2026-09-01 02:31:16',
            ],
            [
                'id' => 2,
                'type' => 'base',
                'name' => 'Personal',
                'price' => '200000.00',
                'duration' => 5,
                'duration_unit' => 'month',
                'features' => '{"branch": "true", "tenant": "5", "discount": "true", "login_register": "true"}',
                'created_at' => '2026-09-01 02:34:11',
                'updated_at' => '2026-09-01 02:37:05',
            ],
            [
                'id' => 3,
                'type' => 'base',
                'name' => 'Profesional',
                'price' => '500000.00',
                'duration' => 1,
                'duration_unit' => 'year',
                'features' => '{"branch": "true", "tenant": "10", "discount": "true", "attendance": "true", "login_register": "true"}',
                'created_at' => '2026-09-01 02:36:32',
                'updated_at' => '2026-09-01 02:36:51',
            ]
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['id' => $plan['id']], $plan);
        }
    }
}
