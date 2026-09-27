<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FulfillmentStatus: string implements HasColor, HasIcon, HasLabel
{
    case Unfulfilled = 'unfulfilled';
    case Processing = 'processing';
    case Fulfilled = 'fulfilled';
    case Failed = 'failed';
    case RefundedToWallet = 'refunded_to_wallet';

    public function getLabel(): string
    {
        return match ($this) {
            self::Unfulfilled => 'Unfulfilled',
            self::Processing => 'Processing Delivery',
            self::Fulfilled => 'Fulfilled',
            self::Failed => 'Fulfillment Failed',
            self::RefundedToWallet => 'Refunded to Wallet',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Unfulfilled => 'gray',
            self::Processing => 'info',
            self::Fulfilled => 'success',
            self::Failed => 'danger',
            self::RefundedToWallet => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Unfulfilled => 'heroicon-m-inbox',
            self::Processing => 'heroicon-m-arrow-path',
            self::Fulfilled => 'heroicon-m-check-badge',
            self::Failed => 'heroicon-m-x-circle',
            self::RefundedToWallet => 'heroicon-m-arrow-uturn-left',
        };
    }
}
