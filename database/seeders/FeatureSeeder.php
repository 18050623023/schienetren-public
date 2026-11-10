<?php

namespace Database\Seeders;

use App\Enums\TenantFeature;
use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $created = 0;
        foreach (TenantFeature::cases() as $case) {
            $feature = Feature::firstOrCreate(
                ['code' => $case->value],
                [
                    'name' => $case->getLabel(),
                    'type' => $case->getType(),
                ]
            );

            if ($feature->wasRecentlyCreated) {
                $created++;
                $this->command->info("Created feature: {$case->value} ({$case->getLabel()})");
            }
        }

        $this->command->info("Total new features created: {$created}");
    }
}
