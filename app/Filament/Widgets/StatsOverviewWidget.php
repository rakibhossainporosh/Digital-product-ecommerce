<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\WalletTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // 1. Today's Sales & Net Profit
        $todayOrders = Order::query()
            ->where('status', OrderStatus::Completed)
            ->where('created_at', '>=', $today)
            ->get();

        $todaySales = $todayOrders->sum('total_amount');
        $todayNetProfit = $todayOrders->sum(function ($order) {
            return (float) $order->total_amount - ((float) $order->cost_price * $order->quantity);
        });

        // 2. This Month's Sales
        $thisMonthSales = Order::query()
            ->where('status', OrderStatus::Completed)
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_amount');

        // 3. Total Wallet Deposits
        $totalDeposits = WalletTransaction::query()
            ->deposits()
            ->sum('amount');

        // 4. New Customers (This month)
        $newCustomers = Customer::query()
            ->where('created_at', '>=', $thisMonth)
            ->count();

        return [
            Stat::make('Today\'s Sales', '৳ '.number_format((float) $todaySales, 2))
                ->description('Net Profit: ৳ '.number_format((float) $todayNetProfit, 2))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('This Month\'s Sales', '৳ '.number_format((float) $thisMonthSales, 2))
                ->description('Completed orders only')
                ->color('primary'),

            Stat::make('Total Wallet Deposits', '৳ '.number_format((float) $totalDeposits, 2))
                ->description('All time successful deposits')
                ->color('warning'),

            Stat::make('New Customers', number_format($newCustomers))
                ->description('Registered this month')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
