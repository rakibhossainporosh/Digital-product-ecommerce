<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'Orders by Status';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $data = [
            $counts[OrderStatus::Completed->value] ?? 0,
            $counts[OrderStatus::Pending->value] ?? 0,
            $counts[OrderStatus::Processing->value] ?? 0,
            $counts[OrderStatus::Refunded->value] ?? 0,
            $counts[OrderStatus::Cancelled->value] ?? 0,
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'backgroundColor' => [
                        '#10b981', // green for completed
                        '#f59e0b', // amber for pending
                        '#3b82f6', // blue for processing
                        '#6b7280', // gray for refunded
                        '#ef4444', // red for cancelled
                    ],
                ],
            ],
            'labels' => ['Completed', 'Pending', 'Processing', 'Refunded', 'Cancelled'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
