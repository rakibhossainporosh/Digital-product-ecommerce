<?php

namespace App\Filament\Resources\Customers\Widgets;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $currentFilter = request()->query('filter');

        $totalCustomers = Customer::count();
        $activeCustomers = Customer::active()->count();
        $totalBalance = (float) Customer::sum('balance');
        $accountsWithBalance = Customer::where('balance', '>', 0)->count();
        $resellersCount = Customer::resellers()->count();
        $avgResellerDiscount = Customer::resellers()->avg('reseller_discount');
        $bannedCount = Customer::banned()->count();

        return [
            Stat::make('Total Customers', $totalCustomers)
                ->url(CustomerResource::getUrl('index'))
                ->description(blank($currentFilter) ? "● {$activeCustomers} active accounts" : 'Click to view all customers')
                ->descriptionIcon(blank($currentFilter) ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-right')
                ->descriptionColor('primary')
                ->color('primary')
                ->chart([5, 12, 28, 45, 70, max($totalCustomers, 10)]),

            Stat::make('Wallet Float / Liability', '৳ '.number_format($totalBalance, 2))
                ->url($currentFilter === 'positive_balance' ? CustomerResource::getUrl('index') : CustomerResource::getUrl('index', ['filter' => 'positive_balance']))
                ->description($currentFilter === 'positive_balance' ? '● Filtering funded accounts' : "{$accountsWithBalance} accounts holding funds")
                ->descriptionIcon($currentFilter === 'positive_balance' ? 'heroicon-m-check-circle' : 'heroicon-m-banknotes')
                ->descriptionColor('success')
                ->color('success')
                ->chart([100, 300, 600, 1200, 2500, max((int) $totalBalance, 10)]),

            Stat::make('Active Resellers', $resellersCount)
                ->url($currentFilter === 'resellers' ? CustomerResource::getUrl('index') : CustomerResource::getUrl('index', ['filter' => 'resellers']))
                ->description($currentFilter === 'resellers' ? '● Filtering wholesale partners' : ($avgResellerDiscount ? number_format((float) $avgResellerDiscount, 1).'% avg discount' : 'Wholesale partners'))
                ->descriptionIcon($currentFilter === 'resellers' ? 'heroicon-m-check-circle' : 'heroicon-m-sparkles')
                ->descriptionColor('warning')
                ->color('warning')
                ->chart([1, 2, 4, 6, 8, max($resellersCount, 2)]),

            Stat::make('Banned / Suspended', $bannedCount)
                ->url($currentFilter === 'banned' ? CustomerResource::getUrl('index') : CustomerResource::getUrl('index', ['filter' => 'banned']))
                ->description($currentFilter === 'banned' ? '● Filtering blocked accounts' : ($bannedCount > 0 ? "{$bannedCount} accounts restricted" : 'Zero accounts blocked'))
                ->descriptionIcon($currentFilter === 'banned' ? 'heroicon-m-check-circle' : 'heroicon-m-shield-check')
                ->descriptionColor($bannedCount > 0 ? 'danger' : 'gray')
                ->color($bannedCount > 0 ? 'danger' : 'gray')
                ->chart([max($bannedCount, 1), 0, 1, 0, max($bannedCount, 1)]),
        ];
    }
}
