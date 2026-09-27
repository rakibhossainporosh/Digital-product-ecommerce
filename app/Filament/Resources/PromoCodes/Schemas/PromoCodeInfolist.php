<?php

namespace App\Filament\Resources\PromoCodes\Schemas;

use App\Models\PromoCode;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class PromoCodeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Campaign & Discount Overview')
                    ->icon('heroicon-m-ticket')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Coupon Code')
                                    ->fontFamily('mono')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->copyable()
                                    ->copyMessage('Coupon code copied to clipboard!')
                                    ->icon('heroicon-m-ticket')
                                    ->color('primary'),

                                TextEntry::make('type')
                                    ->label('Discount Type')
                                    ->badge(),

                                TextEntry::make('value')
                                    ->label('Discount Rate')
                                    ->formatStateUsing(fn (PromoCode $record): string => $record->formatted_discount)
                                    ->weight(FontWeight::ExtraBold)
                                    ->color('success'),

                                TextEntry::make('min_spend')
                                    ->label('Minimum Order Amount')
                                    ->formatStateUsing(fn ($state): string => filled($state) ? '৳ '.number_format((float) $state, 2) : 'None'),

                                TextEntry::make('max_discount')
                                    ->label('Max Discount Cap')
                                    ->formatStateUsing(fn ($state): string => filled($state) ? '৳ '.number_format((float) $state, 2) : 'No Cap'),

                                TextEntry::make('is_active')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Disabled')
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                            ]),
                    ]),

                Section::make('Quotas & Scoping Rules')
                    ->icon('heroicon-m-shield-check')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('usage_progress')
                                    ->label('Usage Counter')
                                    ->weight(FontWeight::Bold)
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('max_uses_per_customer')
                                    ->label('Customer Quota')
                                    ->formatStateUsing(fn ($state): string => "{$state} time(s) per account"),

                                TextEntry::make('exclude_resellers')
                                    ->label('Reseller Access')
                                    ->badge()
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Excluded (Wholesale Only)' : 'Allowed')
                                    ->color(fn (bool $state): string => $state ? 'warning' : 'success'),

                                TextEntry::make('scope_description')
                                    ->label('Product Scope')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('starts_at')
                                    ->label('Campaign Begins')
                                    ->dateTime('M d, Y h:i A')
                                    ->placeholder('Immediate'),

                                TextEntry::make('expires_at')
                                    ->label('Campaign Expires')
                                    ->dateTime('M d, Y h:i A')
                                    ->placeholder('No Expiration'),
                            ]),
                    ]),
            ]);
    }
}
