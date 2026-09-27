<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Product Details')
                    ->tabs([
                        // Tab 1: Product Overview
                        Tab::make('Overview')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Product Title')
                                            ->size(TextSize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->icon('heroicon-m-tag'),

                                        TextEntry::make('status')
                                            ->label('Storefront Status')
                                            ->badge()
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active & Published' : 'Hidden / Draft')
                                            ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                                            ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-badge' : 'heroicon-m-eye-slash'),

                                        TextEntry::make('category.name')
                                            ->label('Category')
                                            ->badge()
                                            ->color('gray')
                                            ->icon('heroicon-m-folder')
                                            ->placeholder('Uncategorized'),

                                        TextEntry::make('price_range')
                                            ->label('Price Range')
                                            ->badge()
                                            ->color('warning')
                                            ->icon('heroicon-m-currency-bangladeshi'),

                                        TextEntry::make('slug')
                                            ->label('Storefront URL')
                                            ->state(fn (Product $record): string => url('/product/'.$record->slug))
                                            ->icon('heroicon-m-arrow-top-right-on-square')
                                            ->color('primary')
                                            ->copyable()
                                            ->copyMessage('Product URL copied to clipboard'),

                                        TextEntry::make('demo_video_url')
                                            ->label('Demo Video')
                                            ->placeholder('No showcase video linked')
                                            ->icon('heroicon-m-play-circle')
                                            ->copyable(),

                                        TextEntry::make('description')
                                            ->label('Description')
                                            ->placeholder('No description provided.')
                                            ->columnSpanFull(),
                                    ]),

                                TextEntry::make('features')
                                    ->label('Feature Highlights')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-check-circle')
                                    ->placeholder('No feature highlights specified'),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('created_at')
                                            ->label('Created On')
                                            ->dateTime('M d, Y · h:i A')
                                            ->helperText(fn (Product $record): ?string => $record->created_at?->diffForHumans()),

                                        TextEntry::make('updated_at')
                                            ->label('Last Updated')
                                            ->dateTime('M d, Y · h:i A')
                                            ->helperText(fn (Product $record): ?string => $record->updated_at?->diffForHumans()),
                                    ]),
                            ]),

                        // Tab 2: Duration Variants Breakdown
                        Tab::make('Variants & Pricing')
                            ->icon('heroicon-m-currency-bangladeshi')
                            ->schema([
                                RepeatableEntry::make('variants')
                                    ->schema([
                                        Grid::make(6)
                                            ->schema([
                                                TextEntry::make('duration_name')
                                                    ->label('Duration Tier')
                                                    ->weight(FontWeight::Bold)
                                                    ->icon('heroicon-m-clock'),

                                                TextEntry::make('duration_days')
                                                    ->label('Duration')
                                                    ->formatStateUsing(fn (int $state): string => "{$state} Days")
                                                    ->badge()
                                                    ->color('info'),

                                                TextEntry::make('available_stock')
                                                    ->label('Stock Inventory')
                                                    ->state(function (ProductVariant $record): string {
                                                        $count = $record->available_keys_count;

                                                        return $count > 0 ? "{$count} in stock" : 'Out of stock (0)';
                                                    })
                                                    ->badge()
                                                    ->color(function (ProductVariant $record): string {
                                                        $count = $record->available_keys_count;

                                                        return $count > 5 ? 'success' : ($count > 0 ? 'warning' : 'danger');
                                                    })
                                                    ->icon(fn (ProductVariant $record): string => $record->available_keys_count > 0 ? 'heroicon-m-key' : 'heroicon-m-exclamation-circle'),

                                                TextEntry::make('regular_price')
                                                    ->label('Retail Price')
                                                    ->state(function (ProductVariant $record): string {
                                                        $reg = '৳'.number_format((float) $record->regular_price, 2);
                                                        if ($record->offer_price && (float) $record->offer_price < (float) $record->regular_price) {
                                                            $off = '৳'.number_format((float) $record->offer_price, 2);

                                                            return "{$off} (Reg: {$reg})";
                                                        }

                                                        return $reg;
                                                    })
                                                    ->badge()
                                                    ->color(fn (ProductVariant $record): string => $record->has_discount ? 'success' : 'gray'),

                                                TextEntry::make('cost_price')
                                                    ->label('Cost & Profit Margin')
                                                    ->state(function (ProductVariant $record): string {
                                                        if ($record->cost_price === null) {
                                                            return 'Not specified';
                                                        }

                                                        $cost = '৳'.number_format((float) $record->cost_price, 2);
                                                        $profit = $record->profit !== null ? ' · Profit: ৳'.$record->profit : '';
                                                        $margin = $record->profit_margin_percentage ? " ({$record->profit_margin_percentage})" : '';

                                                        return "{$cost}{$profit}{$margin}";
                                                    })
                                                    ->badge()
                                                    ->color(fn (ProductVariant $record): string => ($record->profit ?? 0) > 0 ? 'success' : 'warning'),

                                                TextEntry::make('is_popular')
                                                    ->label('Badge / API')
                                                    ->state(function (ProductVariant $record): string {
                                                        $labels = [];
                                                        if ($record->is_popular) {
                                                            $labels[] = '★ Popular';
                                                        }
                                                        if ($record->api_provider_id) {
                                                            $labels[] = "API #{$record->api_provider_id}";
                                                        }

                                                        return ! empty($labels) ? implode(' · ', $labels) : 'Standard';
                                                    })
                                                    ->badge()
                                                    ->color(fn (ProductVariant $record): string => $record->is_popular ? 'warning' : 'gray'),
                                            ]),
                                    ]),
                            ]),

                        // Tab 3: Media & Assets
                        Tab::make('Media & Assets')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                ImageEntry::make('image')
                                    ->label('Cover / Banner Image')
                                    ->disk('public')
                                    ->defaultImageUrl(fn (): string => 'https://ui-avatars.com/api/?name=Product&background=f1f5f9&color=64748b')
                                    ->extraImgAttributes([
                                        'class' => 'rounded-xl shadow-sm border border-gray-100 dark:border-white/10 object-cover max-h-48 w-full',
                                    ]),

                                TextEntry::make('icon')
                                    ->label('Icon Identifier')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-sparkles')
                                    ->placeholder('Default Icon'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
