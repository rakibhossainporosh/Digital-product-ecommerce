<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case Wallet = 'wallet';
    case Gateway = 'gateway';
    case PartialWalletGateway = 'partial_wallet_gateway';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::Wallet => 'Customer Wallet',
            self::Gateway => 'Online Gateway',
            self::PartialWalletGateway => 'Wallet + Gateway',
            self::Manual => 'Manual / Admin',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Wallet => 'success',
            self::Gateway => 'info',
            self::PartialWalletGateway => 'warning',
            self::Manual => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Wallet => 'heroicon-m-wallet',
            self::Gateway => 'heroicon-m-credit-card',
            self::PartialWalletGateway => 'heroicon-m-arrows-right-left',
            self::Manual => 'heroicon-m-user-circle',
        };
    }
}
