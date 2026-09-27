<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum WalletTransactionType: string implements HasColor, HasIcon, HasLabel
{
    case Deposit = 'deposit';
    case Purchase = 'purchase';
    case Refund = 'refund';
    case AdminAdjust = 'admin_adjust';
    case ReferralBonus = 'referral_bonus';

    public function getLabel(): string
    {
        return match ($this) {
            self::Deposit => 'Deposit',
            self::Purchase => 'Purchase',
            self::Refund => 'Refund',
            self::AdminAdjust => 'Admin Adjustment',
            self::ReferralBonus => 'Referral Bonus',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Deposit => 'info',
            self::Purchase => 'primary',
            self::Refund => 'warning',
            self::AdminAdjust => 'gray',
            self::ReferralBonus => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Deposit => 'heroicon-m-arrow-down-tray',
            self::Purchase => 'heroicon-m-shopping-cart',
            self::Refund => 'heroicon-m-arrow-path',
            self::AdminAdjust => 'heroicon-m-adjustments-horizontal',
            self::ReferralBonus => 'heroicon-m-gift',
        };
    }
}
