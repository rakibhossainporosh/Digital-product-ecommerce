<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Profile & Credentials')
                    ->description('Contact identification and authentication credentials.')
                    ->icon('heroicon-m-user')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Full Name')
                                    ->placeholder('e.g. John Doe')
                                    ->prefixIcon('heroicon-m-user')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->placeholder('e.g. customer@example.com')
                                    ->prefixIcon('heroicon-m-envelope')
                                    ->required()
                                    ->email()
                                    ->unique(Customer::class, 'email', ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('whatsapp_number')
                                    ->label('WhatsApp Number')
                                    ->placeholder('+8801700000000')
                                    ->prefixIcon('heroicon-m-phone')
                                    ->tel()
                                    ->maxLength(32),

                                Select::make('status')
                                    ->label('Account Status')
                                    ->options(CustomerStatus::class)
                                    ->default(CustomerStatus::Active)
                                    ->required()
                                    ->native(false),

                                TextInput::make('password')
                                    ->label('Password')
                                    ->placeholder('Enter secure password (min 8 characters)')
                                    ->prefixIcon('heroicon-m-lock-closed')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Leave empty to preserve existing password.' : 'Must contain at least 8 characters.')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Referral & Reseller Privileges')
                    ->description('Configure reseller wholesale status and affiliate attribution.')
                    ->icon('heroicon-m-building-storefront')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('referral_code')
                                    ->label('Personal Referral Code')
                                    ->disabled()
                                    ->placeholder('Auto-generated upon creation')
                                    ->prefixIcon('heroicon-m-gift'),

                                Select::make('referred_by')
                                    ->label('Referred By (Affiliate)')
                                    ->relationship('referrer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Select referring customer')
                                    ->native(false),

                                Toggle::make('is_reseller')
                                    ->label('Reseller Tier')
                                    ->helperText('Grant reseller portal access and wholesale price eligibility.')
                                    ->reactive(),

                                TextInput::make('reseller_discount')
                                    ->label('Global Reseller Discount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->placeholder('e.g. 10.00')
                                    ->visible(fn ($get): bool => (bool) $get('is_reseller'))
                                    ->helperText('Default percentage discount applied across all products without custom rates.'),
                            ]),
                    ]),
            ]);
    }
}
