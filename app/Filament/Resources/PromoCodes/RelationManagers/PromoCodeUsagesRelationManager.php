<?php

namespace App\Filament\Resources\PromoCodes\RelationManagers;

use App\Models\PromoCodeUsage;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromoCodeUsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'usages';

    protected static ?string $title = 'Redemption History & Audit Log';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-clock';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->fontFamily('mono')
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->description(fn (PromoCodeUsage $record): ?string => $record->customer?->email),

                TextColumn::make('discount_amount')
                    ->label('Discount Given')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->weight(FontWeight::ExtraBold)
                    ->color('success'),

                TextColumn::make('order.total_amount')
                    ->label('Order Total')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label('Redeemed At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ]);
    }
}
