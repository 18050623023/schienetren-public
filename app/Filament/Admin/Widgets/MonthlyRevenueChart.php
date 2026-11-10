<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MonthlyRevenueChart extends ChartWidget
{
    public ?string $filter = 'year';
    protected ?string $heading = 'Revenue';
    protected static ?int $sort = 2;

    protected function getFilters(): ?array
    {
        return [
            'week' => '7 Hari Terakhir',
            'month' => '30 Hari Terakhir',
            'year' => '12 Bulan Terakhir',
        ];
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        $activeFilter = $this->filter;
        
        if ($activeFilter === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);
                $labels[] = $day->format('d M');
                $data[] = Invoice::whereDate('created_at', $day)
                    ->where('status', 'paid')
                    ->sum('total_amount');
            }
        } elseif ($activeFilter === 'month') {
            for ($i = 29; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);
                $labels[] = $day->format('d M');
                $data[] = Invoice::whereDate('created_at', $day)
                    ->where('status', 'paid')
                    ->sum('total_amount');
            }
        } else {
            // default to 'year' (last 12 months)
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $labels[] = $month->format('M Y');
                
                $data[] = Invoice::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->where('status', 'paid')
                    ->sum('total_amount');
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $data,
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
