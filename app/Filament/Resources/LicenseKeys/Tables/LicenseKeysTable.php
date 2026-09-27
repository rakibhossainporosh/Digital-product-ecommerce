<?php

namespace App\Filament\Resources\LicenseKeys\Tables;

use App\Enums\LicenseKeyStatus;
use App\Models\LicenseKey;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LicenseKeysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->with(['productVariant.product', 'creator']);

                $filter = request()->query('filter');

                if ($filter === 'available') {
                    $query->where('status', LicenseKeyStatus::Available);
                } elseif ($filter === 'sold') {
                    $query->where('status', LicenseKeyStatus::Sold);
                } elseif ($filter === 'low_stock') {
                    $lowStockVariantIds = ProductVariant::whereDoesntHave('availableLicenseKeys', null, '>=', 5)->pluck('id');

                    $query->whereIn('product_variant_id', $lowStockVariantIds);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('row_index')
                    ->rowIndex()
                    ->label('#')
                    ->toggleable(),

                TextColumn::make('productVariant.product.name')
                    ->label('Product Title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn (LicenseKey $record): ?string => $record->productVariant?->duration_name)
                    ->toggleable(),

                TextColumn::make('productVariant.duration_name')
                    ->label('Tier')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('key')
                    ->label('License Key')
                    ->formatStateUsing(fn (LicenseKey $record): string => $record->masked_key)
                    ->copyable()
                    ->copyableState(fn (LicenseKey $record): string => (string) $record->key)
                    ->copyMessage('License key copied to clipboard')
                    ->copyMessageDuration(2000)
                    ->fontFamily('mono')
                    ->weight('medium')
                    ->icon('heroicon-m-clipboard-document')
                    ->tooltip('Click to copy raw decrypted license key')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (LicenseKeyStatus $state): string => $state->getLabel())
                    ->color(fn (LicenseKeyStatus $state): string => $state->getColor())
                    ->icon(fn (LicenseKeyStatus $state): string => $state->getIcon())
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('batch_ref')
                    ->label('Batch Ref')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('order_id')
                    ->label('Order #')
                    ->formatStateUsing(fn (?int $state): string => $state ? "#{$state}" : '—')
                    ->badge()
                    ->color(fn (?int $state): string => $state ? 'warning' : 'gray')
                    ->toggleable(),

                TextColumn::make('sold_at')
                    ->label('Sold At')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Imported At')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('product_variant_id')
                    ->label('Filter by Product Tier')
                    ->options(function () {
                        return ProductVariant::with('product')
                            ->get()
                            ->mapWithKeys(function (ProductVariant $variant) {
                                return [$variant->id => ($variant->product?->name ?? 'Product').' — '.$variant->duration_name];
                            });
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Inventory Status')
                    ->options(LicenseKeyStatus::class),

                SelectFilter::make('batch_ref')
                    ->label('Batch Reference')
                    ->options(fn () => LicenseKey::whereNotNull('batch_ref')->distinct()->pluck('batch_ref', 'batch_ref')->all())
                    ->searchable(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('xl'),
                EditAction::make()
                    ->modalWidth('xl'),
                Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-m-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Revoke License Key')
                    ->modalDescription('Are you sure you want to revoke this license key? It will no longer be available for sale.')
                    ->visible(fn (LicenseKey $record): bool => $record->status === LicenseKeyStatus::Available)
                    ->action(function (LicenseKey $record): void {
                        $record->update(['status' => LicenseKeyStatus::Revoked]);

                        Notification::make()
                            ->title('License key revoked')
                            ->warning()
                            ->send();
                    }),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_available')
                        ->label('Mark as Available')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => LicenseKeyStatus::Available]);

                            Notification::make()
                                ->title('Selected keys marked as Available')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('mark_revoked')
                        ->label('Mark as Revoked')
                        ->icon('heroicon-m-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => LicenseKeyStatus::Revoked]);

                            Notification::make()
                                ->title('Selected keys revoked')
                                ->warning()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
