<?php

namespace Tests\Unit;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\EmployeeLeaveBalance;
use App\Models\Tenant\HrPayroll;
use App\Models\Tenant\LeaveRequest;
use App\Services\Reports\HrReportService;
use Tests\TestCase;

class HrReportServiceTest extends TestCase
{
    /**
     * Test bahwa HrReportService dapat diinstansiasi dengan benar.
     */
    public function test_hr_report_service_can_be_instantiated(): void
    {
        $service = new HrReportService();
        $this->assertInstanceOf(HrReportService::class, $service);
    }

    /**
     * Test bahwa query scopes pada Attendance terdefinisi dan menghasilkan query builder yang valid.
     */
    public function test_attendance_query_scopes(): void
    {
        $attendance = new Attendance();

        $queryDates = $attendance->betweenDates('2026-10-01', '2026-10-31');
        $this->assertStringContainsString('between', strtolower($queryDates->toSql()));

        $queryBranch = $attendance->forBranch(1);
        $this->assertStringContainsString('employee_assignments', $queryBranch->toSql());

        $queryEmployee = $attendance->forEmployee(5);
        $this->assertStringContainsString('employee_assignments', $queryEmployee->toSql());

        $queryStatus = $attendance->status('present');
        $this->assertStringContainsString('status', $queryStatus->toSql());
    }

    /**
     * Test bahwa query scopes pada LeaveRequest terdefinisi.
     */
    public function test_leave_request_query_scopes(): void
    {
        $leave = new LeaveRequest();

        $queryDates = $leave->betweenDates('2026-10-01', '2026-10-31');
        $this->assertNotEmpty($queryDates->toSql());

        $queryEmployee = $leave->forEmployee(2);
        $this->assertStringContainsString('employee_id', $queryEmployee->toSql());

        $queryStatus = $leave->status('approved');
        $this->assertStringContainsString('status', $queryStatus->toSql());
    }

    /**
     * Test bahwa query scopes pada EmployeeLeaveBalance terdefinisi.
     */
    public function test_employee_leave_balance_query_scopes(): void
    {
        $balance = new EmployeeLeaveBalance();

        $queryEmployee = $balance->forEmployee(2);
        $this->assertStringContainsString('employee_id', $queryEmployee->toSql());

        $queryYear = $balance->forYear('2026');
        $this->assertStringContainsString('period_start', $queryYear->toSql());
    }

    /**
     * Test bahwa query scopes pada HrPayroll terdefinisi.
     */
    public function test_hr_payroll_query_scopes(): void
    {
        $payroll = new HrPayroll();

        $queryPeriod = $payroll->forPeriod('2026-10');
        $this->assertStringContainsString('period', $queryPeriod->toSql());

        $queryBranch = $payroll->forBranch(1);
        $this->assertStringContainsString('branch_id', $queryBranch->toSql());

        $queryStatus = $payroll->status('paid');
        $this->assertStringContainsString('status', $queryStatus->toSql());
    }
}
