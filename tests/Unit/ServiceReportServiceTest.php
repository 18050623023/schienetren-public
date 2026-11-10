<?php

namespace Tests\Unit;

use App\Enums\RepairStatus;
use App\Models\Tenant\Client;
use App\Models\Tenant\Repair;
use App\Models\Tenant\RepairDevice;
use App\Models\Tenant\RepairWarrantyClaim;
use App\Services\Reports\ServiceReportService;
use Tests\TestCase;

class ServiceReportServiceTest extends TestCase
{
    /**
     * Test bahwa ServiceReportService dapat diinstansiasi dengan benar.
     */
    public function test_service_report_service_can_be_instantiated(): void
    {
        $service = new ServiceReportService();
        $this->assertInstanceOf(ServiceReportService::class, $service);
    }

    /**
     * Test bahwa query scopes pada Repair terdefinisi dan menghasilkan query builder yang valid.
     */
    public function test_repair_query_scopes(): void
    {
        $repair = new Repair();

        $queryDates = $repair->betweenDates('2026-10-01', '2026-10-31');
        $sqlDates = $queryDates->toSql();
        $this->assertStringContainsString('created_at', $sqlDates);

        $queryBranch = (new Repair())->forBranch(2);
        $sqlBranch = $queryBranch->toSql();
        $this->assertStringContainsString('branch_id', $sqlBranch);

        $queryStatus = (new Repair())->status(RepairStatus::Delivered->value);
        $sqlStatus = $queryStatus->toSql();
        $this->assertStringContainsString('status', $sqlStatus);

        $queryClient = (new Repair())->forClient(5);
        $sqlClient = $queryClient->toSql();
        $this->assertStringContainsString('client_id', $sqlClient);

        $queryTech = (new Repair())->forTechnician(3);
        $sqlTech = $queryTech->toSql();
        $this->assertStringContainsString('employee_assignment_id', $sqlTech);
    }

    /**
     * Test bahwa query scopes pada RepairDevice terdefinisi dan menghasilkan query SQL yang valid.
     */
    public function test_repair_device_query_scopes(): void
    {
        $device = new RepairDevice();

        $queryDates = $device->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('created_at', $queryDates->toSql());

        $queryBrand = (new RepairDevice())->forBrand(4);
        $this->assertStringContainsString('brand_id', $queryBrand->toSql());

        $queryType = (new RepairDevice())->forDeviceType(1);
        $this->assertStringContainsString('device_type_id', $queryType->toSql());

        $queryStatus = (new RepairDevice())->status('checking');
        $this->assertStringContainsString('status', $queryStatus->toSql());

        $queryBranch = (new RepairDevice())->forBranch(2);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());
    }

    /**
     * Test bahwa query scopes pada RepairWarrantyClaim terdefinisi dan menghasilkan query builder valid.
     */
    public function test_repair_warranty_claim_query_scopes(): void
    {
        $claim = new RepairWarrantyClaim();

        $queryDates = $claim->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('claim_date', $queryDates->toSql());

        $queryStatus = (new RepairWarrantyClaim())->status('completed');
        $this->assertStringContainsString('status', $queryStatus->toSql());

        $queryBranch = (new RepairWarrantyClaim())->forBranch(2);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());
    }

    /**
     * Test bahwa query scopes pada Client terdefinisi dan valid.
     */
    public function test_client_query_scopes(): void
    {
        $client = new Client();

        $queryBranch = $client->forBranch(1);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());

        $queryRepairs = (new Client())->hasRepairs();
        $this->assertStringContainsString('repairs', $queryRepairs->toSql());
    }
}
