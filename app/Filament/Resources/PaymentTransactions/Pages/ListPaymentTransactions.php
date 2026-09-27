<?php

namespace App\Filament\Resources\PaymentTransactions\Pages;

use App\Filament\Resources\PaymentTransactions\PaymentTransactionResource;
use App\Filament\Resources\PaymentTransactions\Widgets\PaymentTransactionStats;
use Filament\Resources\Pages\ListRecords;

class ListPaymentTransactions extends ListRecords
{
    protected static string $resource = PaymentTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions needed here
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PaymentTransactionStats::class,
        ];
    }
}
