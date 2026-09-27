<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use App\Enums\DiscountType;
use App\Models\PromoCode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon Identity & Discount Details')
                    ->description('Set code string, discount type, and value parameters.')
                    ->icon('heroicon-m-ticket')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Coupon Code')
                                    ->placeholder('e.g. EID2026')
                                    ->prefixIcon('heroicon-m-ticket')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(PromoCode::class, 'code', ignoreRecord: true)
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),

                                TextInput::make('description')
                                    ->label('Campaign Description')
                                    ->placeholder('e.g. Eid Ul Fitr promotional 15% discount')
                                    ->maxLength(255),

                                Select::make('type')
                                    ->label('Discount Type')
                                    ->options(DiscountType::class)
                                    ->default(DiscountType::Percentage)
                                    ->required()
                                    ->native(false)
                                    ->reactive(),

                                TextInput::make('value')
                                    ->label('Discount Rate / Amount')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->suffix(fn ($get): string => $get('type') === DiscountType::Percentage->value || $get('type') === 'percentage' ? '%' : '৳')
                                    ->placeholder('e.g. 15.00'),

                                TextInput::make('min_spend')
                                    ->label('Minimum Order Amount (৳)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('৳')
                                    ->placeholder('e.g. 500.00')
                                    ->helperText('Cart subtotal must equal or exceed this amount.'),

                                TextInput::make('max_discount')
                                    ->label('Maximum Discount Cap (৳)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('৳')
                                    ->placeholder('e.g. 300.00')
                                    ->visible(fn ($get): bool => $get('type') === DiscountType::Percentage->value || $get('type') === 'percentage')
                                    ->helperText('Upper monetary limit for percentage discounts.'),
                            ]),
                    ]),

                Section::make('Usage Quotas & Reseller Rules')
                    ->description('Fraud prevention and redemption frequency settings.')
                    ->icon('heroicon-m-shield-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('max_uses')
                                    ->label('Global Total Usage Limit')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Leave empty for unlimited')
                                    ->helperText('Total times this code can be redeemed store-wide.'),

                                TextInput::make('max_uses_per_customer')
                                    ->label('Usage Limit Per Customer')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->helperText('How many times a single customer account may use this code.'),

                                Toggle::make('exclude_resellers')
                                    ->label('Exclude Reseller Accounts')
                                    ->default(true)
                                    ->helperText('Prevent wholesale partner accounts from applying retail promo codes.'),

                                Toggle::make('is_active')
                                    ->label('Coupon Active')
                                    ->default(true)
                                    ->helperText('Enable or disable coupon redemption immediately.'),
                            ]),
                    ]),

                Section::make('Product Scope & Validity Schedule')
                    ->description('Optionally restrict coupon to specific catalog items and date ranges.')
                    ->icon('heroicon-m-calendar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product Restriction')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('All Products (Store-wide)')
                                    ->native(false),

                                Select::make('category_id')
                                    ->label('Category Restriction')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('All Categories')
                                    ->native(false),

                                DateTimePicker::make('starts_at')
                                    ->label('Start Date & Time (Optional)')
                                    ->native(false),

                                DateTimePicker::make('expires_at')
                                    ->label('Expiration Date & Time (Optional)')
                                    ->native(false),
                            ]),
                    ]),
            ]);
    }
}
