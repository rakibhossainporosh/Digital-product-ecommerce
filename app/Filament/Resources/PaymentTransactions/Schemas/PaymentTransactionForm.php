<?php

namespace App\Filament\Resources\PaymentTransactions\Schemas;

use App\Enums\PaymentTransactionStatus;
use App\Enums\PaymentTransactionType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('order_id')
                    ->relationship('order', 'id'),
                Select::make('type')
                    ->options(PaymentTransactionType::class)
                    ->default('deposit')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('currency')
                    ->required()
                    ->default('BDT'),
                TextInput::make('gateway_name')
                    ->required()
                    ->default('uddoktapay'),
                TextInput::make('invoice_id'),
                TextInput::make('gateway_transaction_id'),
                TextInput::make('payment_channel'),
                Select::make('status')
                    ->options(PaymentTransactionStatus::class)
                    ->default('pending')
                    ->required(),
                Textarea::make('payment_url')
                    ->columnSpanFull(),
                TextInput::make('metadata'),
                TextInput::make('raw_payload'),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
