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

        // 7-day sparkline trends
        $salesTrend = [];
        $depositsTrend = [];
        $customersTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $salesTrend[] = (float) (Order::where('status', OrderStatus::Completed)
                ->whereDate('created_at', $date)
                ->sum('total_amount') ?? 0);
            $depositsTrend[] = (float) (WalletTransaction::deposits()
                ->whereDate('created_at', $date)
                ->sum('amount') ?? 0);
            $customersTrend[] = (int) (Customer::whereDate('created_at', $date)->count() ?? 0);
        }

        return [
            Stat::make('Today\'s Sales', '৳ '.number_format((float) $todaySales, 2))
                ->description('Net Profit: ৳ '.number_format((float) $todayNetProfit, 2))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($salesTrend)
                ->color('success'),

            Stat::make('This Month\'s Sales', '৳ '.number_format((float) $thisMonthSales, 2))
                ->description('Completed store revenue')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart($salesTrend)
                ->color('primary'),

            Stat::make('Total Wallet Deposits', '৳ '.number_format((float) $totalDeposits, 2))
                ->description('All time customer top-ups')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($depositsTrend)
                ->color('warning'),

            Stat::make('New Customers', number_format($newCustomers))
                ->description('Registered this month')
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart($customersTrend)
                ->color('info'),
        ];
    }
}
