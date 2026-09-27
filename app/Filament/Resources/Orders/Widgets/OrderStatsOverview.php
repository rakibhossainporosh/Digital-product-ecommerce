<?php

namespace App\Filament\Resources\Orders\Widgets;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $currentFilter = request()->query('filter');

        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('created_at', today())->count();
        $totalRevenue = (float) Order::where('payment_status', PaymentStatus::Paid)->sum('total_amount');
        $pendingOrders = Order::whereIn('status', [OrderStatus::Pending, OrderStatus::Processing])->count();
        $fulfilledOrders = Order::where('fulfillment_status', FulfillmentStatus::Fulfilled)->count();

        return [
            Stat::make('Total Orders', $totalOrders)
                ->url(OrderResource::getUrl('index'))
                ->description(blank($currentFilter) ? "● {$todayOrders} orders today" : 'Click to view all orders')
                ->descriptionIcon(blank($currentFilter) ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-right')
                ->descriptionColor('primary')
                ->color('primary')
                ->chart([5, 15, 30, 60, 90, max($totalOrders, 10)]),

            Stat::make('Gross Revenue', '৳ '.number_format($totalRevenue, 2))
                ->url($currentFilter === 'paid' ? OrderResource::getUrl('index') : OrderResource::getUrl('index', ['filter' => 'paid']))
                ->description($currentFilter === 'paid' ? '● Filtering paid orders' : 'Sales from paid orders')
                ->descriptionIcon($currentFilter === 'paid' ? 'heroicon-m-check-circle' : 'heroicon-m-banknotes')
                ->descriptionColor('success')
                ->color('success')
                ->chart([200, 500, 1200, 2500, 4800, max((int) $totalRevenue, 10)]),

            Stat::make('Pending / Processing', $pendingOrders)
                ->url($currentFilter === 'pending' ? OrderResource::getUrl('index') : OrderResource::getUrl('index', ['filter' => 'pending']))
                ->description($currentFilter === 'pending' ? '● Filtering pending queue' : ($pendingOrders > 0 ? "{$pendingOrders} orders awaiting fulfillment" : 'Queue clean'))
                ->descriptionIcon($currentFilter === 'pending' ? 'heroicon-m-check-circle' : 'heroicon-m-clock')
                ->descriptionColor($pendingOrders > 0 ? 'warning' : 'gray')
                ->color($pendingOrders > 0 ? 'warning' : 'gray')
                ->chart([max($pendingOrders, 1), 2, 0, 1, max($pendingOrders, 1)]),

            Stat::make('Fulfilled & Delivered', $fulfilledOrders)
                ->url($currentFilter === 'fulfilled' ? OrderResource::getUrl('index') : OrderResource::getUrl('index', ['filter' => 'fulfilled']))
                ->description($currentFilter === 'fulfilled' ? '● Filtering delivered orders' : "{$fulfilledOrders} keys delivered")
                ->descriptionIcon($currentFilter === 'fulfilled' ? 'heroicon-m-check-circle' : 'heroicon-m-check-badge')
                ->descriptionColor('success')
                ->color('success')
                ->chart([4, 10, 25, 50, 80, max($fulfilledOrders, 8)]),
        ];
    }
}
