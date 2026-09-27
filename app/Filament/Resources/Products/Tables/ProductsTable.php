<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\LicenseKeyStatus;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->with(['category', 'variants.licenseKeys']);

                $filter = request()->query('filter');

                if ($filter === 'active') {
                    $query->where('status', true);
                } elseif ($filter === 'inactive') {
                    $query->where('status', false);
                } elseif ($filter === 'popular') {
                    $query->whereHas('variants', fn (Builder $q): Builder => $q->where('is_popular', true));
                }

                return $query;
            })
            ->columns([
                TextColumn::make('row_index')
                    ->rowIndex()
                    ->label('#')
                    ->toggleable(),

                ImageColumn::make('image')
                    ->label('Thumbnail')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn (): string => 'https://ui-avatars.com/api/?name=Prod&background=f1f5f9&color=64748b')
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Product Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record): ?string => $record->category?->name)
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('price_range')
                    ->label('Price Range')
                    ->badge()
                    ->color('warning')
                    ->toggleable(),

                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Tiers')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('stock_status')
                    ->label('Stock')
                    ->state(function (Product $record): string {
                        $available = $record->variants->sum(fn ($v) => $v->licenseKeys->where('status', LicenseKeyStatus::Available)->count());

                        return $available > 0 ? "{$available} in stock" : 'Out of stock';
                    })
                    ->badge()
                    ->color(function (Product $record): string {
                        $available = $record->variants->sum(fn ($v) => $v->licenseKeys->where('status', LicenseKeyStatus::Available)->count());

                        return $available > 10 ? 'success' : ($available > 0 ? 'warning' : 'danger');
                    })
                    ->icon(function (Product $record): string {
                        $available = $record->variants->sum(fn ($v) => $v->licenseKeys->where('status', LicenseKeyStatus::Available)->count());

                        return $available > 0 ? 'heroicon-m-key' : 'heroicon-m-exclamation-circle';
                    })
                    ->toggleable(),

                IconColumn::make('status')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Filter by Category')
                    ->preload()
                    ->searchable(),

                TernaryFilter::make('status')
                    ->label('Storefront Status')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only')
                    ->placeholder('All Products'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
