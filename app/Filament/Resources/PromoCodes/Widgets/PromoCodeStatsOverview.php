<?php

namespace App\Filament\Resources\PromoCodes\Widgets;

use App\Filament\Resources\PromoCodes\PromoCodeResource;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PromoCodeStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $currentFilter = request()->query('filter');

        $totalCodes = PromoCode::count();
        $activeCodes = PromoCode::active()->count();
        $totalSavings = (float) PromoCodeUsage::sum('discount_amount');
        $totalRedemptions = PromoCodeUsage::count();
        $validNowCount = PromoCode::validNow()->count();

        return [
            Stat::make('Total Promo Codes', $totalCodes)
                ->url(PromoCodeResource::getUrl('index'))
                ->description(blank($currentFilter) ? "● {$activeCodes} active coupons" : 'Click to view all coupons')
                ->descriptionIcon(blank($currentFilter) ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-right')
                ->descriptionColor('primary')
                ->color('primary')
                ->chart([3, 7, 12, 18, 25, max($totalCodes, 5)]),

            Stat::make('Total Customer Savings', '৳ '.number_format($totalSavings, 2))
                ->description('Discounts distributed across orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->descriptionColor('success')
                ->color('success')
                ->chart([50, 150, 400, 900, 1800, max((int) $totalSavings, 10)]),

            Stat::make('Total Redemptions', $totalRedemptions)
                ->description("{$totalRedemptions} successful checkouts")
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->descriptionColor('info')
                ->color('info')
                ->chart([1, 4, 8, 15, 22, max($totalRedemptions, 3)]),

            Stat::make('Live Active Campaigns', $validNowCount)
                ->url($currentFilter === 'active' ? PromoCodeResource::getUrl('index') : PromoCodeResource::getUrl('index', ['filter' => 'active']))
                ->description($currentFilter === 'active' ? '● Filtering active campaigns' : 'Available for immediate checkout')
                ->descriptionIcon($currentFilter === 'active' ? 'heroicon-m-check-circle' : 'heroicon-m-sparkles')
                ->descriptionColor('success')
                ->color('success')
                ->chart([2, 5, 8, 12, max($validNowCount, 2)]),
        ];
    }
}
