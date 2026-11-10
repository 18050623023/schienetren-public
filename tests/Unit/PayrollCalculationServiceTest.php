<?php

namespace Tests\Unit;

use App\Enums\CompensationPackageComponentBasedOn;
use App\Models\Tenant\CompensationPackageComponent;
use App\Services\PayrollCalculationService;
use Tests\TestCase;

class PayrollCalculationServiceTest extends TestCase
{
    /**
     * Test bahwa PayrollCalculationService dapat diinstansiasi dan enum terdefinisi lengkap.
     */
    public function test_service_instantiation_and_enum_cases(): void
    {
        $service = new PayrollCalculationService();
        $this->assertInstanceOf(PayrollCalculationService::class, $service);

        $cases = CompensationPackageComponentBasedOn::cases();
        $expectedValues = [
            'fixed',
            'basic_salary',
            'repair_service',
            'repair_item',
            'sales',
            'attendance',
            'overtime_hour',
            'custom',
        ];

        $actualValues = array_map(fn ($case) => $case->value, $cases);
        $this->assertEqualsCanonicalizing($expectedValues, $actualValues);
    }

    /**
     * Test relasi component pada CompensationPackageComponent menggunakan foreign key yang benar.
     */
    public function test_compensation_package_component_has_correct_component_foreign_key(): void
    {
        $cpc = new CompensationPackageComponent();
        $relation = $cpc->component();

        $this->assertEquals('compensation_component_id', $relation->getForeignKeyName());
    }
}
