<?php

namespace App\Services\Reports;

use App\Enums\RepairStatus;
use App\Models\Tenant\Brand;
use App\Models\Tenant\Client;
use App\Models\Tenant\DeviceType;
use App\Models\Tenant\EmployeeAssignment;
use App\Models\Tenant\Repair;
use App\Models\Tenant\RepairDevice;
use App\Models\Tenant\RepairTechnician;
use App\Models\Tenant\RepairWarrantyClaim;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServiceReportService
{
    /**
     * 1. Laporan Operasional & Tiket Servis
     */
    public function getServiceOperationsReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $status = $filters['status'] ?? null;
        $technicianId = $filters['technician_id'] ?? null;

        $query = Repair::query()
            ->forBranch($branchId)
            ->betweenDates($dateFrom, $dateTo)
            ->status($status)
            ->forTechnician($technicianId)
            ->with([
                'client',
                'branch',
                'repairInvoice',
                'repairDevices.deviceType',
                'repairDevices.brand',
                'repairDevices.technicians.employeeAssignment.employee.user',
            ])
            ->latest('created_at');

        $repairs = $query->get();

        $totalTickets = $repairs->count();
        $deliveredCount = 0;
        $inServiceCount = 0;
        $canceledCount = 0;
        $totalDevices = 0;
        $totalRevenue = 0;
        $totalPaid = 0;
        $tatDaysSum = 0;
        $tatCount = 0;

        $rows = [];

        foreach ($repairs as $repair) {
            $deviceCount = $repair->repairDevices->count();
            $totalDevices += $deviceCount;

            $statusVal = $repair->status instanceof RepairStatus ? $repair->status->value : (string) $repair->status;

            if ($statusVal === RepairStatus::Delivered->value) {
                $deliveredCount++;
            } elseif (in_array($statusVal, [RepairStatus::Cancel->value, RepairStatus::NotPossible->value])) {
                $canceledCount++;
            } else {
                $inServiceCount++;
            }

            $invoice = $repair->repairInvoice;
            $grandTotal = (float) ($invoice?->grand_total ?? 0);
            $paidAmount = (float) ($invoice?->paid_amount ?? 0);

            $totalRevenue += $grandTotal;
            $totalPaid += $paidAmount;

            // Turnaround time
            $tatDays = null;
            $endDate = $repair->delivered_at;
            if (!$endDate) {
                // Alternatif: tanggal selesai perangkat terakhir
                $latestFinish = $repair->repairDevices->max('finished_at');
                if ($latestFinish) {
                    $endDate = Carbon::parse($latestFinish);
                }
            }

            if ($endDate && $repair->created_at) {
                $created = Carbon::parse($repair->created_at);
                $end = Carbon::parse($endDate);
                $diff = $created->diffInHours($end) / 24;
                $tatDays = round($diff, 1);
                $tatDaysSum += $tatDays;
                $tatCount++;
            }

            // Ringkasan perangkat
            $deviceSummaries = $repair->repairDevices->map(function ($device) {
                $brandName = $device->brand?->name ?? '';
                $type = $device->deviceType?->name ?? '';
                $model = $device->model ?? '';
                return trim("{$type} {$brandName} {$model}");
            })->filter()->implode(', ');

            // Teknisi penanggung jawab
            $technicians = $repair->repairDevices->flatMap(function ($device) {
                return $device->technicians->map(function ($tech) {
                    return $tech->employeeAssignment?->employee?->name;
                });
            })->filter()->unique()->implode(', ');

            $statusObj = $repair->status instanceof RepairStatus ? $repair->status : RepairStatus::tryFrom($statusVal);

            $rows[] = [
                'id' => $repair->id,
                'code' => $repair->code,
                'date' => Carbon::parse($repair->created_at)->format('d/m/Y H:i'),
                'client_name' => $repair->client?->name ?? '-',
                'client_phone' => $repair->client?->phone ?? '-',
                'branch_name' => $repair->branch?->name ?? '-',
                'devices_summary' => $deviceSummaries ?: '-',
                'device_count' => $deviceCount,
                'technicians' => $technicians ?: '-',
                'status' => $statusVal,
                'status_label' => $statusObj ? $statusObj->label() : ucfirst($statusVal),
                'status_color' => $statusObj ? $statusObj->colors() : 'gray',
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'tat_days' => $tatDays,
            ];
        }

        $avgTatDays = $tatCount > 0 ? round($tatDaysSum / $tatCount, 1) : 0;
        $completionRate = $totalTickets > 0 ? round(($deliveredCount / $totalTickets) * 100, 1) : 0;

        return [
            'summary' => [
                'total_tickets' => $totalTickets,
                'total_devices' => $totalDevices,
                'delivered_count' => $deliveredCount,
                'in_service_count' => $inServiceCount,
                'canceled_count' => $canceledCount,
                'completion_rate' => $completionRate,
                'total_revenue' => $totalRevenue,
                'total_paid' => $totalPaid,
                'avg_tat_days' => $avgTatDays,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * 2. Laporan Kinerja & Komisi Teknisi
     */
    public function getTechnicianPerformanceReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $technicianId = $filters['technician_id'] ?? null;

        $query = RepairTechnician::query()
            ->with([
                'repairDevice.repair.branch',
                'repairDevice.repair.repairInvoice',
                'repairDevice.deviceType',
                'employeeAssignment.employee.user',
                'commissions',
            ])
            ->whereHas('repairDevice.repair', function ($q) use ($branchId, $dateFrom, $dateTo) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
                if ($dateFrom) {
                    $q->whereDate('created_at', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $q->whereDate('created_at', '<=', $dateTo);
                }
            });

        if ($technicianId) {
            $query->where('employee_assignment_id', $technicianId);
        }

        $technicianJobs = $query->get();

        // Kelompokkan per teknisi (employee_assignment_id)
        $grouped = $technicianJobs->groupBy('employee_assignment_id');

        $rows = [];
        $totalCommissionAll = 0;
        $totalJobsAll = 0;
        $totalCompletedAll = 0;

        foreach ($grouped as $assignmentId => $jobs) {
            $firstJob = $jobs->first();
            $assignment = $firstJob->employeeAssignment;
            $techName = $assignment?->employee?->name ?? 'Teknisi #' . $assignmentId;
            $branchName = $assignment?->branch?->name ?? '-';

            $totalAssigned = $jobs->count();
            $completedCount = 0;
            $canceledCount = 0;
            $inProgressCount = 0;
            $commissionSum = 0;
            $tatSum = 0;
            $tatCount = 0;

            foreach ($jobs as $job) {
                $device = $job->repairDevice;
                $repair = $device?->repair;
                $statusVal = $repair?->status instanceof RepairStatus ? $repair->status->value : (string) $repair?->status;

                if ($statusVal === RepairStatus::Delivered->value) {
                    $completedCount++;
                } elseif (in_array($statusVal, [RepairStatus::Cancel->value, RepairStatus::NotPossible->value])) {
                    $canceledCount++;
                } else {
                    $inProgressCount++;
                }

                // Komisi
                $commAmount = (float) $job->commission_amount;
                if ($commAmount <= 0 && $job->commissions->isNotEmpty()) {
                    $commAmount = (float) $job->commissions->sum('amount');
                }
                $commissionSum += $commAmount;

                // Turnaround
                if ($repair?->delivered_at && $repair?->created_at) {
                    $created = Carbon::parse($repair->created_at);
                    $end = Carbon::parse($repair->delivered_at);
                    $diff = $created->diffInHours($end) / 24;
                    $tatSum += $diff;
                    $tatCount++;
                }
            }

            $successRate = $totalAssigned > 0 ? round(($completedCount / $totalAssigned) * 100, 1) : 0;
            $avgTat = $tatCount > 0 ? round($tatSum / $tatCount, 1) : 0;

            $totalCommissionAll += $commissionSum;
            $totalJobsAll += $totalAssigned;
            $totalCompletedAll += $completedCount;

            $rows[] = [
                'assignment_id' => $assignmentId,
                'technician_name' => $techName,
                'branch_name' => $branchName,
                'total_assigned' => $totalAssigned,
                'completed_count' => $completedCount,
                'in_progress_count' => $inProgressCount,
                'canceled_count' => $canceledCount,
                'success_rate' => $successRate,
                'avg_tat_days' => $avgTat,
                'total_commission' => $commissionSum,
            ];
        }

        // Urutkan teknisi berdasarkan jumlah servis selesai terbanyak
        usort($rows, fn($a, $b) => $b['completed_count'] <=> $a['completed_count']);

        $overallSuccessRate = $totalJobsAll > 0 ? round(($totalCompletedAll / $totalJobsAll) * 100, 1) : 0;

        return [
            'summary' => [
                'total_technicians' => count($rows),
                'total_jobs_assigned' => $totalJobsAll,
                'total_completed' => $totalCompletedAll,
                'overall_success_rate' => $overallSuccessRate,
                'total_commission_paid' => $totalCommissionAll,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * 3. Laporan Tren Kerusakan & Perangkat
     */
    public function getDeviceFaultAnalyticsReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $brandId = $filters['brand_id'] ?? null;
        $deviceTypeId = $filters['device_type_id'] ?? null;

        $query = RepairDevice::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->forBrand($brandId)
            ->forDeviceType($deviceTypeId)
            ->with([
                'brand',
                'deviceType',
                'repair.branch',
                'repairItems.productVariant.product',
            ]);

        $devices = $query->get();
        $totalDevices = $devices->count();

        // 1. Distribusi per Brand
        $brandGrouped = $devices->groupBy(fn($d) => $d->brand?->name ?? 'Lain-lain');
        $brandsBreakdown = [];
        foreach ($brandGrouped as $brandName => $items) {
            $cnt = $items->count();
            $pct = $totalDevices > 0 ? round(($cnt / $totalDevices) * 100, 1) : 0;
            $brandsBreakdown[] = [
                'name' => $brandName,
                'count' => $cnt,
                'percentage' => $pct,
            ];
        }
        usort($brandsBreakdown, fn($a, $b) => $b['count'] <=> $a['count']);

        // 2. Distribusi per Device Type
        $typeGrouped = $devices->groupBy(fn($d) => $d->deviceType?->name ?? 'Lainnya');
        $typesBreakdown = [];
        foreach ($typeGrouped as $typeName => $items) {
            $cnt = $items->count();
            $pct = $totalDevices > 0 ? round(($cnt / $totalDevices) * 100, 1) : 0;
            $typesBreakdown[] = [
                'name' => $typeName,
                'count' => $cnt,
                'percentage' => $pct,
            ];
        }
        usort($typesBreakdown, fn($a, $b) => $b['count'] <=> $a['count']);

        // 3. Top Models
        $modelGrouped = $devices->groupBy(function ($d) {
            $brand = $d->brand?->name ?? '';
            $model = $d->model ?? '-';
            return trim("{$brand} {$model}");
        });
        $modelsBreakdown = [];
        foreach ($modelGrouped as $modelName => $items) {
            $cnt = $items->count();
            $pct = $totalDevices > 0 ? round(($cnt / $totalDevices) * 100, 1) : 0;
            $sampleProblems = $items->pluck('problems')->filter()->take(3)->implode('; ');
            $modelsBreakdown[] = [
                'model' => $modelName,
                'count' => $cnt,
                'percentage' => $pct,
                'sample_problems' => $sampleProblems ?: '-',
            ];
        }
        usort($modelsBreakdown, fn($a, $b) => $b['count'] <=> $a['count']);
        $topModels = array_slice($modelsBreakdown, 0, 15);

        // 4. Masalah Paling Sering (Common Problems)
        $problemList = $devices->map(function ($d) {
            return [
                'device' => trim(($d->brand?->name ?? '') . ' ' . ($d->model ?? '')),
                'problems' => $d->problems ?: '-',
                'diagnosis' => $d->diagnosis ?: '-',
                'date' => Carbon::parse($d->created_at)->format('d/m/Y'),
            ];
        })->filter(fn($p) => $p['problems'] !== '-')->take(20)->values()->all();

        return [
            'summary' => [
                'total_devices' => $totalDevices,
                'total_brands' => count($brandsBreakdown),
                'top_brand' => $brandsBreakdown[0]['name'] ?? '-',
                'top_brand_pct' => $brandsBreakdown[0]['percentage'] ?? 0,
                'top_device_type' => $typesBreakdown[0]['name'] ?? '-',
                'top_model' => $topModels[0]['model'] ?? '-',
            ],
            'brands' => $brandsBreakdown,
            'types' => $typesBreakdown,
            'top_models' => $topModels,
            'recent_problems' => $problemList,
        ];
    }

    /**
     * 4. Laporan Analitik Pelanggan & CRM
     */
    public function getCustomerInsightsReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $repairsQuery = Repair::query()
            ->forBranch($branchId)
            ->betweenDates($dateFrom, $dateTo)
            ->with(['client', 'repairInvoice', 'repairDevices']);

        $repairs = $repairsQuery->get();

        // Kelompokkan berdasarkan client_id
        $grouped = $repairs->groupBy('client_id');

        $rows = [];
        $totalSpendAll = 0;
        $repeatCustomerCount = 0;

        foreach ($grouped as $clientId => $clientRepairs) {
            $client = $clientRepairs->first()->client;
            if (!$client) {
                continue;
            }

            $ticketCount = $clientRepairs->count();
            $deviceCount = $clientRepairs->sum(fn($r) => $r->repairDevices->count());

            $totalSpend = (float) $clientRepairs->sum(function ($r) {
                return $r->repairInvoice?->grand_total ?? 0;
            });
            $totalSpendAll += $totalSpend;

            $firstVisit = $clientRepairs->min('created_at');
            $lastVisit = $clientRepairs->max('created_at');

            $isRepeat = $ticketCount > 1;
            if ($isRepeat) {
                $repeatCustomerCount++;
            }

            // WhatsApp formatting (0812 -> 62812)
            $rawWa = $client->whatsapp ?: $client->phone;
            $cleanWa = preg_replace('/[^0-9]/', '', (string) $rawWa);
            if (str_starts_with($cleanWa, '0')) {
                $cleanWa = '62' . substr($cleanWa, 1);
            }

            $rows[] = [
                'client_id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone ?: '-',
                'whatsapp' => $client->whatsapp ?: '-',
                'wa_link' => $cleanWa ? "https://wa.me/{$cleanWa}" : null,
                'address' => $client->address ?: '-',
                'ticket_count' => $ticketCount,
                'device_count' => $deviceCount,
                'total_spend' => $totalSpend,
                'first_visit' => Carbon::parse($firstVisit)->format('d/m/Y'),
                'last_visit' => Carbon::parse($lastVisit)->format('d/m/Y'),
                'is_repeat' => $isRepeat,
            ];
        }

        // Urutkan berdasarkan total belanja tertinggi (LTV)
        usort($rows, fn($a, $b) => $b['total_spend'] <=> $a['total_spend']);

        $uniqueClientsCount = count($rows);
        $repeatRate = $uniqueClientsCount > 0 ? round(($repeatCustomerCount / $uniqueClientsCount) * 100, 1) : 0;
        $avgSpendPerClient = $uniqueClientsCount > 0 ? round($totalSpendAll / $uniqueClientsCount, 0) : 0;

        return [
            'summary' => [
                'total_unique_clients' => $uniqueClientsCount,
                'repeat_clients_count' => $repeatCustomerCount,
                'repeat_rate' => $repeatRate,
                'total_revenue' => $totalSpendAll,
                'avg_spend_per_client' => $avgSpendPerClient,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * 5. Laporan Klaim Garansi & Retur RMA
     */
    public function getWarrantyClaimReport(array $filters): array
    {
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $status = $filters['status'] ?? null;

        $query = RepairWarrantyClaim::query()
            ->betweenDates($dateFrom, $dateTo)
            ->forBranch($branchId)
            ->status($status)
            ->with([
                'repairItemWarranty.repairItem.repairDevice.repair.client',
                'repairItemWarranty.repairItem.repairDevice.repair.branch',
                'repairItemWarranty.repairItem.productVariant.product',
                'newRepair.branch',
                'handledBy',
            ])
            ->latest('claim_date');

        $claims = $query->get();

        $totalClaims = $claims->count();
        $completedCount = 0;
        $rejectedCount = 0;
        $pendingCount = 0;
        $totalRefund = 0;

        $rows = [];

        foreach ($claims as $claim) {
            $origRepair = $claim->repairItemWarranty?->repairItem?->repairDevice?->repair;
            $newRepair = $claim->newRepair;
            $client = $origRepair?->client;
            $item = $claim->repairItemWarranty?->repairItem;
            $product = $item?->productVariant?->product;

            $statusVal = (string) $claim->status;
            if ($statusVal === 'completed') {
                $completedCount++;
            } elseif ($statusVal === 'rejected') {
                $rejectedCount++;
            } else {
                $pendingCount++;
            }

            $refund = (float) ($claim->refund_amount ?? 0);
            $totalRefund += $refund;

            $statusColor = match ($statusVal) {
                'completed' => 'success',
                'rejected' => 'danger',
                'in_progress' => 'warning',
                default => 'primary',
            };

            $statusLabel = match ($statusVal) {
                'completed' => 'Selesai / Diganti',
                'rejected' => 'Ditolak',
                'in_progress' => 'Sedang Diproses',
                default => 'Menunggu Review',
            };

            $rows[] = [
                'id' => $claim->id,
                'claim_date' => Carbon::parse($claim->claim_date)->format('d/m/Y'),
                'original_ticket' => $origRepair?->code ?? '-',
                'new_ticket' => $newRepair?->code ?? '-',
                'client_name' => $client?->name ?? '-',
                'branch_name' => $origRepair?->branch?->name ?? $newRepair?->branch?->name ?? '-',
                'item_name' => $product?->name ?? 'Jasa / Sparepart',
                'problems' => $claim->problems ?: '-',
                'solution' => $claim->solution ?: '-',
                'status' => $statusVal,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'refund_amount' => $refund,
                'handler_name' => $claim->handledBy?->name ?? '-',
            ];
        }

        // Hitung estimasi RMA rate terhadap total servis dalam periode yang sama
        $totalRepairsInPeriod = Repair::query()
            ->forBranch($branchId)
            ->betweenDates($dateFrom, $dateTo)
            ->where('status', RepairStatus::Delivered)
            ->count();

        $rmaRate = $totalRepairsInPeriod > 0 ? round(($totalClaims / $totalRepairsInPeriod) * 100, 1) : 0;

        return [
            'summary' => [
                'total_claims' => $totalClaims,
                'completed_count' => $completedCount,
                'rejected_count' => $rejectedCount,
                'pending_count' => $pendingCount,
                'total_refund' => $totalRefund,
                'delivered_repairs_count' => $totalRepairsInPeriod,
                'rma_rate' => $rmaRate,
            ],
            'rows' => $rows,
        ];
    }

    /**
     * 6. Stream Export CSV (UTF-8 BOM)
     */
    public function exportCsv(string $tab, array $filters): StreamedResponse
    {
        $filename = 'Laporan-Servis-' . ucfirst($tab) . '-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($tab, $filters) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM untuk kompatibilitas instan dengan Microsoft Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            switch ($tab) {
                case 'technicians':
                    $data = $this->getTechnicianPerformanceReport($filters);
                    fputcsv($handle, ['Nama Teknisi', 'Cabang', 'Total Unit Ditangani', 'Selesai', 'Dalam Proses', 'Batal', 'Tingkat Keberhasilan (%)', 'Rata-rata Durasi (Hari)', 'Total Komisi (Rp)']);
                    foreach ($data['rows'] as $r) {
                        fputcsv($handle, [
                            $r['technician_name'],
                            $r['branch_name'],
                            $r['total_assigned'],
                            $r['completed_count'],
                            $r['in_progress_count'],
                            $r['canceled_count'],
                            $r['success_rate'] . '%',
                            $r['avg_tat_days'],
                            $r['total_commission'],
                        ]);
                    }
                    break;

                case 'fault_trends':
                    $data = $this->getDeviceFaultAnalyticsReport($filters);
                    fputcsv($handle, ['Tipe Model / Seri Perangkat', 'Jumlah Unit Masuk', 'Persentase (%)', 'Contoh Masalah / Gejala']);
                    foreach ($data['top_models'] as $m) {
                        fputcsv($handle, [
                            $m['model'],
                            $m['count'],
                            $m['percentage'] . '%',
                            $m['sample_problems'],
                        ]);
                    }
                    break;

                case 'crm':
                    $data = $this->getCustomerInsightsReport($filters);
                    fputcsv($handle, ['Nama Pelanggan', 'No Telepon', 'No WhatsApp', 'Alamat', 'Jumlah Tiket', 'Jumlah Perangkat', 'Total Belanja Servis (Rp)', 'Kunjungan Pertama', 'Kunjungan Terakhir', 'Status']);
                    foreach ($data['rows'] as $c) {
                        fputcsv($handle, [
                            $c['name'],
                            $c['phone'],
                            $c['whatsapp'],
                            $c['address'],
                            $c['ticket_count'],
                            $c['device_count'],
                            $c['total_spend'],
                            $c['first_visit'],
                            $c['last_visit'],
                            $c['is_repeat'] ? 'Pelanggan Berulang' : 'Pelanggan Baru',
                        ]);
                    }
                    break;

                case 'warranty':
                    $data = $this->getWarrantyClaimReport($filters);
                    fputcsv($handle, ['ID Klaim', 'Tanggal Klaim', 'Tiket Awal', 'Tiket Baru RMA', 'Pelanggan', 'Cabang', 'Komponen / Jasa', 'Masalah', 'Solusi', 'Status', 'Nominal Refund (Rp)', 'Petugas']);
                    foreach ($data['rows'] as $w) {
                        fputcsv($handle, [
                            $w['id'],
                            $w['claim_date'],
                            $w['original_ticket'],
                            $w['new_ticket'],
                            $w['client_name'],
                            $w['branch_name'],
                            $w['item_name'],
                            $w['problems'],
                            $w['solution'],
                            $w['status_label'],
                            $w['refund_amount'],
                            $w['handler_name'],
                        ]);
                    }
                    break;

                case 'operations':
                default:
                    $data = $this->getServiceOperationsReport($filters);
                    fputcsv($handle, ['No Tiket', 'Tanggal Masuk', 'Pelanggan', 'No Telepon', 'Cabang', 'Rincian Perangkat', 'Jml Unit', 'Teknisi', 'Status', 'Total Biaya (Rp)', 'Sudah Dibayar (Rp)', 'Durasi Selesai (Hari)']);
                    foreach ($data['rows'] as $row) {
                        fputcsv($handle, [
                            $row['code'],
                            $row['date'],
                            $row['client_name'],
                            $row['client_phone'],
                            $row['branch_name'],
                            $row['devices_summary'],
                            $row['device_count'],
                            $row['technicians'],
                            $row['status_label'],
                            $row['grand_total'],
                            $row['paid_amount'],
                            $row['tat_days'] ?? '-',
                        ]);
                    }
                    break;
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
