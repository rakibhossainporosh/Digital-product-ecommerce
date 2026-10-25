<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue Trend (Last 30 Days)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'xl' => 2,
    ];

    protected ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        $orders = Order::query()
            ->where('status', OrderStatus::Completed)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays(29 - $i);
            $dateString = $date->format('Y-m-d');

            $labels[] = $date->format('M d');
            $data[] = $orders->get($dateString) ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (৳)',
                    'data' => $data,
                    'fill' => 'start',
                    'tension' => 0.35,
                    'backgroundColor' => 'rgba(248, 40, 3, 0.08)',
                    'borderColor' => '#f82803',
                    'pointBackgroundColor' => '#f82803',
                    'pointBorderColor' => '#ffffff',
                    'pointHoverRadius' => 6,
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
