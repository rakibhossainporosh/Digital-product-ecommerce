<?php

namespace App\Filament\Resources\Products\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $currentFilter = request()->query('filter');

        $totalProducts = Product::count();
        $activeProducts = Product::where('status', true)->count();
        $inactiveProducts = $totalProducts - $activeProducts;
        $totalTiers = ProductVariant::count();
        $popularTiers = ProductVariant::where('is_popular', true)->count();

        return [
            Stat::make('Total Products', $totalProducts)
                ->url(ProductResource::getUrl('index'))
                ->description(blank($currentFilter) ? '● Viewing all products' : "Click to show all ({$totalTiers} tiers)")
                ->descriptionIcon(blank($currentFilter) ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-right')
                ->descriptionColor('primary')
                ->color('primary')
                ->chart([2, 4, 3, 5, 6, 5, max($totalProducts, 4)]),

            Stat::make('Active Storefront', $activeProducts)
                ->url($currentFilter === 'active' ? ProductResource::getUrl('index') : ProductResource::getUrl('index', ['filter' => 'active']))
                ->description($currentFilter === 'active' ? '● Filtering active only' : 'Click to filter live products')
                ->descriptionIcon($currentFilter === 'active' ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-right')
                ->descriptionColor('success')
                ->color('success')
                ->chart([2, 3, 4, 4, 5, 6, max($activeProducts, 4)]),

            Stat::make('Inactive / Drafts', $inactiveProducts)
                ->url($currentFilter === 'inactive' ? ProductResource::getUrl('index') : ProductResource::getUrl('index', ['filter' => 'inactive']))
                ->description($currentFilter === 'inactive' ? '● Filtering drafts/hidden' : ($inactiveProducts > 0 ? 'Click to review hidden items' : 'No draft items'))
                ->descriptionIcon($currentFilter === 'inactive' ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-right')
                ->descriptionColor($inactiveProducts > 0 ? 'danger' : 'gray')
                ->color($inactiveProducts > 0 ? 'danger' : 'gray')
                ->chart([max($inactiveProducts, 1), 1, 0, 1, 0, max($inactiveProducts, 0)]),

            Stat::make('Popular Badges', $popularTiers)
                ->url($currentFilter === 'popular' ? ProductResource::getUrl('index') : ProductResource::getUrl('index', ['filter' => 'popular']))
                ->description($currentFilter === 'popular' ? '● Filtering popular items' : 'Click to filter popular tiers')
                ->descriptionIcon($currentFilter === 'popular' ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-right')
                ->descriptionColor('warning')
                ->color('warning')
                ->chart([1, 2, 2, 3, 3, 4, max($popularTiers, 2)]),
        ];
    }
}
