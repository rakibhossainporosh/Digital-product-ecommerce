<?php

namespace App\Filament\Resources\LicenseKeys\Schemas;

use App\Enums\LicenseKeyStatus;
use App\Models\ProductVariant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LicenseKeyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('product_variant_id')
                    ->label('Product & Duration Tier')
                    ->options(function () {
                        return ProductVariant::with('product')
                            ->get()
                            ->mapWithKeys(function (ProductVariant $variant) {
                                $label = ($variant->product?->name ?? 'Product').' — '.$variant->duration_name.' ('.$variant->formatted_regular_price.')';

                                return [$variant->id => $label];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->prefixIcon('heroicon-m-tag')
                    ->columnSpanFull(),

                TextInput::make('key')
                    ->label('License Key / Activation Code')
                    ->placeholder('e.g. VIP-XXXX-XXXX-XXXX')
                    ->prefixIcon('heroicon-m-key')
                    ->required()
                    ->maxLength(1000)
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Inventory Status')
                    ->options(LicenseKeyStatus::class)
                    ->default(LicenseKeyStatus::Available)
                    ->required()
                    ->prefixIcon('heroicon-m-check-badge')
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                TextInput::make('batch_ref')
                    ->label('Batch Reference / Supplier ID')
                    ->placeholder('e.g. BATCH-2026-OCT-01')
                    ->prefixIcon('heroicon-m-hashtag')
                    ->columnSpan([
                        'default' => 2,
                        'sm' => 1,
                    ]),

                Textarea::make('notes')
                    ->label('Internal Notes')
                    ->placeholder('Optional supplier or inventory notes...')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }
}
