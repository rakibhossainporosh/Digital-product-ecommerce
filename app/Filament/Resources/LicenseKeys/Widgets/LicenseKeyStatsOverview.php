<?php

namespace App\Filament\Resources\LicenseKeys\Widgets;

use App\Filament\Resources\LicenseKeys\LicenseKeyResource;
use App\Models\LicenseKey;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LicenseKeyStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $currentFilter = request()->query('filter');

        $totalKeys = LicenseKey::count();
        $availableKeys = LicenseKey::available()->count();
        $soldKeys = LicenseKey::sold()->count();
        $revokedKeys = LicenseKey::revoked()->count();

        // Count variants that have less than 5 available keys
        $lowStockVariantsCount = ProductVariant::whereDoesntHave('availableLicenseKeys', null, '>=', 5)->count();

        return [
            Stat::make('Total Keys', $totalKeys)
                ->url(LicenseKeyResource::getUrl('index'))
                ->description(blank($currentFilter) ? '● Viewing all inventory' : 'Click to view all keys')
                ->descriptionIcon(blank($currentFilter) ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-right')
                ->descriptionColor('primary')
                ->color('primary')
                ->chart([10, 20, 40, 60, 80, 90, max($totalKeys, 10)]),

            Stat::make('Available Stock', $availableKeys)
                ->url($currentFilter === 'available' ? LicenseKeyResource::getUrl('index') : LicenseKeyResource::getUrl('index', ['filter' => 'available']))
                ->description($currentFilter === 'available' ? '● Filtering available only' : 'Ready for instant delivery')
                ->descriptionIcon($currentFilter === 'available' ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-right')
                ->descriptionColor('success')
                ->color('success')
                ->chart([5, 15, 30, 50, 70, 80, max($availableKeys, 8)]),

            Stat::make('Total Sold', $soldKeys)
                ->url($currentFilter === 'sold' ? LicenseKeyResource::getUrl('index') : LicenseKeyResource::getUrl('index', ['filter' => 'sold']))
                ->description($currentFilter === 'sold' ? '● Filtering sold keys' : 'Delivered to customers')
                ->descriptionIcon($currentFilter === 'sold' ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-right')
                ->descriptionColor('info')
                ->color('info')
                ->chart([2, 5, 8, 12, 16, 20, max($soldKeys, 5)]),

            Stat::make('Low Stock Variants', $lowStockVariantsCount)
                ->url($currentFilter === 'low_stock' ? LicenseKeyResource::getUrl('index') : LicenseKeyResource::getUrl('index', ['filter' => 'low_stock']))
                ->description($currentFilter === 'low_stock' ? '● Filtering low stock tiers' : "{$lowStockVariantsCount} tiers have < 5 keys")
                ->descriptionIcon($currentFilter === 'low_stock' ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->descriptionColor($lowStockVariantsCount > 0 ? 'warning' : 'gray')
                ->color($lowStockVariantsCount > 0 ? 'warning' : 'gray')
                ->chart([max($lowStockVariantsCount, 1), 2, 1, 3, 2, max($lowStockVariantsCount, 1)]),
        ];
    }
}
