<?php

namespace App\Filament\Resources\PaymentTransactions\Schemas;

use Filament\Schemas\Components\KeyValueEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Transaction Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('uuid')
                            ->label('Transaction ID')
                            ->copyable()
                            ->fontFamily('mono'),
                        TextEntry::make('customer.name'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('type')
                            ->badge(),
                        TextEntry::make('amount')
                            ->money('BDT'),
                        TextEntry::make('fee')
                            ->money('BDT'),
                        TextEntry::make('payment_channel')
                            ->badge(),
                        TextEntry::make('invoice_id'),
                        TextEntry::make('gateway_transaction_id')
                            ->label('Gateway TrxID'),
                        TextEntry::make('paid_at')
                            ->dateTime(),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ]),

                Section::make('Linked Order')
                    ->hidden(fn ($record) => ! $record->order_id)
                    ->schema([
                        TextEntry::make('order_id')
                            ->label('Order ID'),
                        TextEntry::make('order.status')
                            ->label('Order Status')
                            ->badge(),
                        // Can be expanded to link directly to the order resource
                    ]),

                Section::make('Raw Payload')
                    ->schema([
                        KeyValueEntry::make('metadata')
                            ->keyLabel('Property')
                            ->valueLabel('Value'),
                        KeyValueEntry::make('raw_payload')
                            ->label('Gateway Webhook Payload')
                            ->keyLabel('Property')
                            ->valueLabel('Value'),
                    ])->collapsible(),
            ]);
    }
}
