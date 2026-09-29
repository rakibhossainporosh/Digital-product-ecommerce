<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Summary & Customer Details')
                    ->icon('heroicon-m-shopping-bag')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('order_number')
                                    ->label('Order Number')
                                    ->fontFamily('mono')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->copyable()
                                    ->copyMessage('Order number copied!')
                                    ->icon('heroicon-m-ticket'),

                                TextEntry::make('customer.name')
                                    ->label('Customer Name')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-user'),

                                TextEntry::make('customer.email')
                                    ->label('Customer Email')
                                    ->copyable()
                                    ->icon('heroicon-m-envelope')
                                    ->color('primary'),

                                TextEntry::make('status')
                                    ->label('Order Status')
                                    ->badge(),

                                TextEntry::make('payment_status')
                                    ->label('Payment Status')
                                    ->badge(),

                                TextEntry::make('fulfillment_status')
                                    ->label('Fulfillment Status')
                                    ->badge(),
                            ]),
                    ]),

                Section::make('Product & Financial Calculation')
                    ->icon('heroicon-m-banknotes')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('Product Title')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('productVariant.duration_name')
                                    ->label('Variant Tier')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('quantity')
                                    ->label('Quantity Purchased')
                                    ->badge(),

                                TextEntry::make('unit_price')
                                    ->label('Base Unit Price')
                                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2)),

                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2)),

                                TextEntry::make('discount_amount')
                                    ->label('Discount Applied')
                                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                                    ->color(fn ($state): string => (float) $state > 0 ? 'warning' : 'gray'),

                                TextEntry::make('total_amount')
                                    ->label('Final Amount Paid')
                                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::ExtraBold)
                                    ->color('success'),

                                TextEntry::make('net_profit')
                                    ->label('Estimated Net Profit')
                                    ->formatStateUsing(fn (Order $record): string => $record->net_profit !== null ? '৳ '.number_format((float) $record->net_profit, 2) : 'N/A')
                                    ->weight(FontWeight::Bold)
                                    ->color(fn (Order $record): string => ($record->net_profit ?? 0) >= 0 ? 'success' : 'danger'),
                            ]),
                    ]),

                Section::make('Payment Method & Delivery Audit')
                    ->icon('heroicon-m-shield-check')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('payment_method')
                                    ->label('Payment Method')
                                    ->badge(),

                                TextEntry::make('wallet_amount_paid')
                                    ->label('Paid from Wallet')
                                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2)),

                                TextEntry::make('gateway_transaction_id')
                                    ->label('Gateway Txn ID')
                                    ->placeholder('N/A')
                                    ->fontFamily('mono')
                                    ->copyable(),

                                TextEntry::make('created_at')
                                    ->label('Placed At')
                                    ->dateTime('M d, Y h:i A')
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('fulfilled_at')
                                    ->label('Delivered At')
                                    ->dateTime('M d, Y h:i A')
                                    ->placeholder('Not yet fulfilled')
                                    ->icon('heroicon-m-check-badge'),

                                TextEntry::make('admin_notes')
                                    ->label('System / Admin Notes')
                                    ->placeholder('No notes recorded')
                                    ->columnSpan(3),
                            ]),
                    ]),

                Section::make('Service Details (Manual Fulfillment)')
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->visible(fn (Order $record): bool => ! empty($record->service_data))
                    ->schema([
                        KeyValueEntry::make('service_data')
                            ->label('Service Request Form Data')
                            ->keyLabel('Field')
                            ->valueLabel('Input')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
