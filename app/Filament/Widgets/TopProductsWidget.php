<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Product;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProductsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top Selling Products';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Product::query()
                ->withSum(['orders as total_sold' => function ($query) {
                    $query->where('status', OrderStatus::Completed);
                }], 'quantity')
                ->withSum(['orders as total_revenue' => function ($query) {
                    $query->where('status', OrderStatus::Completed);
                }], 'total_amount')
                ->whereHas('orders', function ($query) {
                    $query->where('status', OrderStatus::Completed);
                })
                ->orderByDesc('total_sold')
                ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->weight(FontWeight::Bold),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge(),

                TextColumn::make('total_sold')
                    ->label('Units Sold')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_revenue')
                    ->label('Revenue Generated')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->color('success')
                    ->weight(FontWeight::Bold)
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
