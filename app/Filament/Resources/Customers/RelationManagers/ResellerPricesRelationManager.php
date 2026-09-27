<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Models\Customer;
use App\Models\ProductVariant;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ResellerPricesRelationManager extends RelationManager
{
    protected static string $relationship = 'resellerPrices';

    protected static ?string $title = 'Custom Reseller Variant Pricing';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-tag';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        /** @var Customer $ownerRecord */
        return (bool) $ownerRecord->is_reseller;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('productVariant.product.name')
                    ->label('Product')
                    ->weight(FontWeight::Bold)
                    ->searchable(),

                TextColumn::make('productVariant.title')
                    ->label('Variant Tier')
                    ->badge()
                    ->color('info'),

                TextColumn::make('productVariant.regular_price')
                    ->label('Regular Price')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->color('gray'),

                TextColumn::make('productVariant.offer_price')
                    ->label('Standard Offer')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2)),

                TextColumn::make('discount_percentage')
                    ->label('Custom Discount')
                    ->formatStateUsing(fn ($state): string => filled($state) ? "{$state}% Off" : '—')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('custom_price')
                    ->label('Override Price')
                    ->formatStateUsing(fn ($state): string => filled($state) ? '৳ '.number_format((float) $state, 2) : '—')
                    ->weight(FontWeight::Bold)
                    ->color('success'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Set Variant Custom Price')
                    ->icon('heroicon-m-plus')
                    ->modalWidth('md')
                    ->form([
                        Select::make('product_variant_id')
                            ->label('Product Variant')
                            ->options(function () {
                                return ProductVariant::with('product')
                                    ->get()
                                    ->mapWithKeys(function (ProductVariant $variant) {
                                        $productName = $variant->product?->name ?? 'Unknown';
                                        $label = "{$productName} — {$variant->title} (৳ ".number_format((float) $variant->effective_price, 2).')';

                                        return [$variant->id => $label];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->native(false),

                        TextInput::make('discount_percentage')
                            ->label('Custom Discount Percentage')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->placeholder('e.g. 15.00')
                            ->helperText('Custom % discount for this variant.'),

                        TextInput::make('custom_price')
                            ->label('Or Exact Fixed Custom Price')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('৳')
                            ->placeholder('e.g. 450.00')
                            ->helperText('Fixed override price in BDT. Takes precedence if specified.'),
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->modalWidth('md')
                    ->form([
                        TextInput::make('discount_percentage')
                            ->label('Custom Discount Percentage')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),

                        TextInput::make('custom_price')
                            ->label('Or Exact Fixed Custom Price')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('৳'),
                    ]),
                DeleteAction::make(),
            ]);
    }
}
