<?php

namespace App\Services\Reports;

use App\Models\Tenant\Attendance;
use App\Models\Tenant\Employee;
use App\Models\Tenant\EmployeeAssignment;
use App\Models\Tenant\EmployeeLeaveBalance;
use App\Models\Tenant\HrPayroll;
use App\Models\Tenant\LeaveRequest;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrReportService
{
    /**
     * Mengambil data rekapitulasi presensi (KPI dan ringkasan per karyawan).
     */
    public function getAttendanceReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->toDateString();
        $employeeId = $filters['employee_id'] ?? null;

        $query = Attendance::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->forEmployee($employeeId)
            ->with([
                'employeeAssignment.employee.user',
                'employeeAssignment.branch',
                'logs',
            ]);

        $attendances = $query->orderBy('date', 'desc')->get();

        // 1. Agregasi KPI Keseluruhan
        $totalRecords = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->whereNotIn('attendance_type', ['leave', 'off_day'])->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $absentCount = $attendances->where('status', 'absent')->count();
        $leaveCount = $attendances->where('attendance_type', 'leave')->count();

        $totalOvertimeMinutes = (int) $attendances->sum('overtime');
        $totalLateMinutes = (int) $attendances->flatMap->logs->sum('late_minutes');
        $totalEarlyMinutes = (int) $attendances->flatMap->logs->sum('early_minutes');

        // 2. Agregasi Rincian per Karyawan
        $grouped = $attendances->groupBy(fn ($item) => $item->employeeAssignment->employee_id ?? 0);

        $employeeRows = [];
        foreach ($grouped as $empId => $records) {
            if (!$empId) {
                continue;
            }
            $first = $records->first();
            $employee = $first->employeeAssignment->employee ?? null;
            $branch = $first->employeeAssignment->branch ?? null;

            $empPresent = $records->where('status', 'present')->whereNotIn('attendance_type', ['leave', 'off_day'])->count();
            $empLate = $records->where('status', 'late')->count();
            $empAbsent = $records->where('status', 'absent')->count();
            $empLeave = $records->where('attendance_type', 'leave')->count();
            $empLateMinutes = (int) $records->flatMap->logs->sum('late_minutes');
            $empEarlyMinutes = (int) $records->flatMap->logs->sum('early_minutes');
            $empOvertimeMinutes = (int) $records->sum('overtime');

            $totalActiveAttendances = $empPresent + $empLate;
            $totalRecordedDays = $records->whereNotIn('attendance_type', ['off_day'])->count();
            $attendanceRate = $totalRecordedDays > 0 ? round(($totalActiveAttendances / $totalRecordedDays) * 100, 1) : 0;

            $employeeRows[] = [
                'employee_id' => $empId,
                'name' => $employee?->user?->name ?? 'N/A',
                'code_pegawai' => $employee?->code_pegawai ?? '-',
                'branch_name' => $branch?->name ?? '-',
                'present_days' => $empPresent,
                'late_days' => $empLate,
                'late_minutes' => $empLateMinutes,
                'early_minutes' => $empEarlyMinutes,
                'overtime_minutes' => $empOvertimeMinutes,
                'leave_days' => $empLeave,
                'absent_days' => $empAbsent,
                'attendance_rate' => $attendanceRate,
            ];
        }

        // Urutkan berdasarkan nama karyawan
        usort($employeeRows, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return [
            'kpi' => [
                'total_records' => $totalRecords,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'late_minutes' => $totalLateMinutes,
                'early_minutes' => $totalEarlyMinutes,
                'overtime_minutes' => $totalOvertimeMinutes,
                'leave_count' => $leaveCount,
                'absent_count' => $absentCount,
            ],
            'rows' => $employeeRows,
            'attendances' => $attendances,
            'filters' => [
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'employee_id' => $employeeId,
            ],
        ];
    }

    /**
     * Mengambil data rekapitulasi cuti dan permohonan izin karyawan.
     */
    public function getLeaveReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfYear()->toDateString();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfYear()->toDateString();
        $employeeId = $filters['employee_id'] ?? null;

        // 1. Data Pengajuan Cuti (Leave Requests)
        $requests = LeaveRequest::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->forEmployee($employeeId)
            ->with([
                'employee.user',
                'leaveType',
                'approvedBy',
                'rejectedBy',
            ])
            ->orderBy('start_date', 'desc')
            ->get();

        // 2. Data Sisa Kuota Cuti (Employee Leave Balance)
        $balances = EmployeeLeaveBalance::query()
            ->forBranch($branchId)
            ->forEmployee($employeeId)
            ->with([
                'employee.user',
                'leaveType',
            ])
            ->get();

        // 3. Kalkulasi KPI
        $totalRequests = $requests->count();
        $approvedRequests = $requests->where('status', 'approved')->count();
        $pendingRequests = $requests->where('status', 'pending')->count();
        $rejectedRequests = $requests->where('status', 'rejected')->count();
        $totalDaysUsed = (float) $requests->where('status', 'approved')->sum('total_days');
        $totalQuotaRemaining = (float) $balances->sum('remaining');

        return [
            'kpi' => [
                'total_requests' => $totalRequests,
                'approved_requests' => $approvedRequests,
                'pending_requests' => $pendingRequests,
                'rejected_requests' => $rejectedRequests,
                'total_days_used' => $totalDaysUsed,
                'total_quota_remaining' => $totalQuotaRemaining,
            ],
            'requests' => $requests,
            'balances' => $balances,
            'filters' => [
                'branch_id' => $branchId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'employee_id' => $employeeId,
            ],
        ];
    }

    /**
     * Mengambil data rekapitulasi penggajian (Payroll & Kompensasi).
     */
    public function getPayrollReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $period = $filters['period'] ?? Carbon::now()->format('Y-m');
        $employeeId = $filters['employee_id'] ?? null;
        $status = $filters['status'] ?? null;

        $query = HrPayroll::query()
            ->forPeriod($period)
            ->forBranch($branchId)
            ->forEmployee($employeeId)
            ->status($status)
            ->with([
                'employee.user',
                'branch',
                'paymentMethod',
                'items',
            ]);

        $payrolls = $query->orderBy('created_at', 'desc')->get();

        // Kalkulasi KPI
        $totalNetSalary = (float) $payrolls->sum('net_salary');
        $totalBasicSalary = (float) $payrolls->sum('basic_salary');
        $totalAllowance = (float) $payrolls->sum('total_allowance');
        $totalDeduction = (float) $payrolls->sum('total_deduction');
        $paidCount = $payrolls->where('status', 'paid')->count();
        $draftCount = $payrolls->where('status', 'draft')->count();

        return [
            'kpi' => [
                'total_net_salary' => $totalNetSalary,
                'total_basic_salary' => $totalBasicSalary,
                'total_allowance' => $totalAllowance,
                'total_deduction' => $totalDeduction,
                'total_slips' => $payrolls->count(),
                'paid_count' => $paidCount,
                'draft_count' => $draftCount,
            ],
            'payrolls' => $payrolls,
            'filters' => [
                'branch_id' => $branchId,
                'period' => $period,
                'employee_id' => $employeeId,
                'status' => $status,
            ],
        ];
    }

    /**
     * Ekspor CSV Native untuk Laporan Presensi.
     */
    public function exportAttendanceCsv(array $filters): StreamedResponse
    {
        $report = $this->getAttendanceReport($filters);
        $fileName = 'laporan-presensi-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            // Tambahkan UTF-8 BOM untuk kompatibilitas Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Header Kolom CSV
            fputcsv($handle, [
                'No',
                'Nama Karyawan',
                'Kode Pegawai',
                'Cabang',
                'Hadir Tepat Waktu (Hari)',
                'Terlambat (Hari)',
                'Total Menit Terlambat',
                'Total Menit Pulang Cepat',
                'Total Menit Lembur',
                'Cuti / Izin (Hari)',
                'Alpha (Hari)',
                'Tingkat Kehadiran (%)',
            ]);

            $no = 1;
            foreach ($report['rows'] as $row) {
                fputcsv($handle, [
                    $no++,
                    $row['name'],
                    $row['code_pegawai'],
                    $row['branch_name'],
                    $row['present_days'],
                    $row['late_days'],
                    $row['late_minutes'],
                    $row['early_minutes'],
                    $row['overtime_minutes'],
                    $row['leave_days'],
                    $row['absent_days'],
                    $row['attendance_rate'] . '%',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Ekspor CSV Native untuk Laporan Cuti.
     */
    public function exportLeaveCsv(array $filters): StreamedResponse
    {
        $report = $this->getLeaveReport($filters);
        $fileName = 'laporan-cuti-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Nama Karyawan',
                'Jenis Cuti',
                'Tanggal Mulai',
                'Tanggal Selesai',
                'Durasi (Hari)',
                'Status',
                'Alasan',
                'Disetujui / Ditolak Oleh',
            ]);

            $no = 1;
            foreach ($report['requests'] as $req) {
                fputcsv($handle, [
                    $no++,
                    $req->employee?->user?->name ?? 'N/A',
                    $req->leaveType?->name ?? '-',
                    $req->start_date,
                    $req->end_date,
                    $req->total_days,
                    ucfirst($req->status),
                    $req->reason,
                    $req->approvedBy?->name ?? $req->rejectedBy?->name ?? '-',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Ekspor CSV Native untuk Laporan Penggajian.
     */
    public function exportPayrollCsv(array $filters): StreamedResponse
    {
        $report = $this->getPayrollReport($filters);
        $fileName = 'laporan-penggajian-' . ($filters['period'] ?? date('Y-m')) . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'No',
                'Periode',
                'Nama Karyawan',
                'Cabang',
                'Gaji Pokok',
                'Tunjangan & Komisi',
                'Potongan',
                'Gaji Bersih',
                'Metode Pembayaran',
                'Status',
            ]);

            $no = 1;
            foreach ($report['payrolls'] as $p) {
                fputcsv($handle, [
                    $no++,
                    $p->period,
                    $p->employee?->user?->name ?? 'N/A',
                    $p->branch?->name ?? '-',
                    $p->basic_salary,
                    $p->total_allowance,
                    $p->total_deduction,
                    $p->net_salary,
                    $p->paymentMethod?->name ?? '-',
                    strtoupper($p->status),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
