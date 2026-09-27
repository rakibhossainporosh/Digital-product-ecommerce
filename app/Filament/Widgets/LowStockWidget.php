<?php

namespace App\Filament\Widgets;

use App\Enums\LicenseKeyStatus;
use App\Models\ProductVariant;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Low Stock Alerts (≤ 5 Keys)';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ProductVariant::query()
                ->withCount(['licenseKeys as available_keys_count' => function ($query) {
                    $query->where('status', LicenseKeyStatus::Available);
                }])
                ->whereHas('licenseKeys', function ($query) {
                    $query->where('status', LicenseKeyStatus::Available);
                }, '<=', 5)
                ->orderBy('available_keys_count', 'asc')
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->weight(FontWeight::Bold),

                TextColumn::make('duration_name')
                    ->label('Variant')
                    ->badge()
                    ->color('info'),

                TextColumn::make('available_keys_count')
                    ->label('Available Keys')
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : 'warning')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
