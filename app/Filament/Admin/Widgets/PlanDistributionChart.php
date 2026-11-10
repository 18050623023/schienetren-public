<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Plan;
use App\Models\Subscription;
use Filament\Widgets\ChartWidget;

class PlanDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Plan Distribution';
    protected static ?int $sort = 3;

    protected function getFilters(): ?array
    {
        return [
            'all' => 'Semua Waktu',
            'today' => 'Hari Ini',
            'week' => 'Minggu Ini',
            'month' => 'Bulan Ini',
            'year' => 'Tahun Ini',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $plans = Plan::withCount(['subscriptions' => function ($query) use ($activeFilter) {
            $query->where('status', 'active');

            if ($activeFilter === 'today') {
                $query->whereDate('created_at', today());
            } elseif ($activeFilter === 'week') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($activeFilter === 'month') {
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            } elseif ($activeFilter === 'year') {
                $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
            }
        }])->get();

        $labels = $plans->pluck('name')->toArray();
        $data = $plans->pluck('subscriptions_count')->toArray();

        // Generate dynamic colors based on number of plans
        $colors = [
            '#34d399', '#60a5fa', '#fbbf24', '#f87171', '#a78bfa', '#2dd4bf', '#fb7185'
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Active Subscriptions',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
