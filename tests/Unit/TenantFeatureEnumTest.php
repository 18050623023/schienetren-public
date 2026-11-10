<?php

namespace Tests\Unit;

use App\Enums\TenantFeature;
use Tests\TestCase;

class TenantFeatureEnumTest extends TestCase
{
    public function test_all_expected_tenant_features_exist(): void
    {
        $expectedCases = [
            'tenant',
            'max_user',
            'max_branch',
            'max_product',
            'max_monthly_repair',
            'kanban_technician',
            'pos',
            'purchase_order',
            'stock_transfer',
            'stock_opname',
            'finance_advance',
            'leave',
            'holiday',
            'shift_management',
            'payroll',
            'attendance_geofence',
            'skill_management',
            'advanced_reports',
        ];

        $actualCases = array_map(fn (TenantFeature $case) => $case->value, TenantFeature::cases());

        foreach ($expectedCases as $expected) {
            $this->assertContains($expected, $actualCases, "Missing expected TenantFeature case: {$expected}");
        }
    }

    public function test_feature_types_and_defaults(): void
    {
        $limitFeatures = [
            TenantFeature::TENANT,
            TenantFeature::MAX_USER,
            TenantFeature::MAX_BRANCH,
            TenantFeature::MAX_PRODUCT,
            TenantFeature::MAX_MONTHLY_REPAIR,
        ];

        foreach ($limitFeatures as $feature) {
            $this->assertEquals('limit', $feature->getType(), "Feature {$feature->value} should be of type 'limit'");
            $this->assertEquals(0, $feature->getDefaultValue(), "Feature {$feature->value} default value should be 0");
        }

        $booleanFeatures = [
            TenantFeature::KANBAN_TECHNICIAN,
            TenantFeature::POS,
            TenantFeature::PURCHASE_ORDER,
            TenantFeature::STOCK_TRANSFER,
            TenantFeature::STOCK_OPNAME,
            TenantFeature::FINANCE_ADVANCE,
            TenantFeature::LEAVE,
            TenantFeature::HOLIDAY,
            TenantFeature::SHIFT_MANAGEMENT,
            TenantFeature::PAYROLL,
            TenantFeature::ATTENDANCE_GEOFENCE,
            TenantFeature::SKILL_MANAGEMENT,
            TenantFeature::ADVANCED_REPORTS,
        ];

        foreach ($booleanFeatures as $feature) {
            $this->assertEquals('boolean', $feature->getType(), "Feature {$feature->value} should be of type 'boolean'");
            $this->assertFalse($feature->getDefaultValue(), "Feature {$feature->value} default value should be false");
        }
    }

    public function test_defaults_array_has_all_features(): void
    {
        $defaults = TenantFeature::getDefaults();
        $this->assertCount(count(TenantFeature::cases()), $defaults);

        $this->assertEquals(0, $defaults['max_user']);
        $this->assertEquals(0, $defaults['max_product']);
        $this->assertFalse($defaults['finance_advance']);
        $this->assertFalse($defaults['pos']);
        $this->assertFalse($defaults['payroll']);
    }

    public function test_get_label_returns_non_empty_readable_strings(): void
    {
        foreach (TenantFeature::cases() as $case) {
            $label = $case->getLabel();
            $this->assertNotEmpty($label, "Label for {$case->value} should not be empty");
            $this->assertIsString($label);
        }

        $this->assertEquals('Keuangan Lanjutan (Double-Entry & Jurnal)', TenantFeature::FINANCE_ADVANCE->getLabel());
        $this->assertEquals('Kasir Penjualan Ritel (POS)', TenantFeature::POS->getLabel());
        $this->assertEquals('Penggajian & Kompensasi (Payroll)', TenantFeature::PAYROLL->getLabel());
    }
}
