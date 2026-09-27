<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Models\Customer;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Profile & Financial Overview')
                    ->icon('heroicon-m-user-circle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Customer Name')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-user'),

                                TextEntry::make('email')
                                    ->label('Email Address')
                                    ->copyable()
                                    ->copyMessage('Email copied!')
                                    ->icon('heroicon-m-envelope')
                                    ->color('primary'),

                                TextEntry::make('whatsapp_number')
                                    ->label('WhatsApp Contact')
                                    ->icon('heroicon-m-phone')
                                    ->placeholder('Not provided')
                                    ->url(fn (Customer $record): ?string => $record->whatsapp_number ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $record->whatsapp_number) : null)
                                    ->openUrlInNewTab(),

                                TextEntry::make('balance')
                                    ->label('Current Wallet Balance')
                                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::ExtraBold)
                                    ->color('success')
                                    ->icon('heroicon-m-wallet'),

                                TextEntry::make('status')
                                    ->label('Account Status')
                                    ->badge(),

                                TextEntry::make('is_reseller')
                                    ->label('Membership Tier')
                                    ->badge()
                                    ->formatStateUsing(fn (Customer $record): string => $record->is_reseller ? 'Reseller ('.($record->reseller_discount ?? 0).'% Off)' : 'Standard Customer')
                                    ->color(fn (Customer $record): string => $record->is_reseller ? 'warning' : 'gray')
                                    ->icon(fn (Customer $record): string => $record->is_reseller ? 'heroicon-m-sparkles' : 'heroicon-m-user'),
                            ]),
                    ]),

                Section::make('Affiliate & Account Activity')
                    ->icon('heroicon-m-gift')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('referral_code')
                                    ->label('Personal Referral Code')
                                    ->badge()
                                    ->color('info')
                                    ->copyable()
                                    ->copyMessage('Referral code copied!')
                                    ->icon('heroicon-m-share'),

                                TextEntry::make('referrer.name')
                                    ->label('Referred By')
                                    ->placeholder('Direct / Organic')
                                    ->icon('heroicon-m-arrow-uturn-left'),

                                TextEntry::make('created_at')
                                    ->label('Registration Date')
                                    ->dateTime('M d, Y h:i A')
                                    ->icon('heroicon-m-calendar'),
                            ]),
                    ]),
            ]);
    }
}
