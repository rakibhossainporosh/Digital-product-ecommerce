<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentTransactionType: string implements HasLabel
{
    case Deposit = 'deposit';
    case OrderPayment = 'order_payment';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Deposit => 'Wallet Deposit',
            self::OrderPayment => 'Order Payment',
        };
    }
}
