<?php

namespace App\Filament\Resources\PromoCodes\Tables;

use App\Enums\DiscountType;
use App\Models\PromoCode;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PromoCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->with(['product', 'category']);

                $filter = request()->query('filter');

                if ($filter === 'active') {
                    $query->validNow();
                }

                return $query;
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('row_index')
                    ->rowIndex()
                    ->label('#')
                    ->toggleable(),

                TextColumn::make('code')
                    ->label('Code')
                    ->fontFamily('mono')
                    ->weight(FontWeight::Bold)
                    ->copyable()
                    ->copyMessage('Promo code copied!')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable()
                    ->description(fn (PromoCode $record): ?string => $record->description),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('value')
                    ->label('Discount')
                    ->formatStateUsing(fn (PromoCode $record): string => $record->formatted_discount)
                    ->weight(FontWeight::ExtraBold)
                    ->color('success')
                    ->sortable(),

                TextColumn::make('usage_progress')
                    ->label('Usage')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('scope_description')
                    ->label('Applicable To')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('min_spend')
                    ->label('Min Spend')
                    ->formatStateUsing(fn ($state): string => filled($state) ? '৳ '.number_format((float) $state, 2) : '—')
                    ->color('gray'),

                TextColumn::make('max_discount')
                    ->label('Cap')
                    ->formatStateUsing(fn ($state): string => filled($state) ? '৳ '.number_format((float) $state, 2) : '—')
                    ->color('warning'),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M d, Y')
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Discount Type')
                    ->options(DiscountType::class)
                    ->native(false),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All Codes')
                    ->trueLabel('Active Codes Only')
                    ->falseLabel('Disabled Codes Only')
                    ->native(false),

                TernaryFilter::make('exclude_resellers')
                    ->label('Reseller Policy')
                    ->placeholder('All Policies')
                    ->trueLabel('Resellers Excluded')
                    ->falseLabel('Resellers Permitted')
                    ->native(false),

                TrashedFilter::make()->native(false),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
