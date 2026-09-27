<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DiscountType: string implements HasColor, HasIcon, HasLabel
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Percentage => 'Percentage (%)',
            self::Fixed => 'Fixed Amount (৳)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Percentage => 'info',
            self::Fixed => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Percentage => 'heroicon-m-receipt-percent',
            self::Fixed => 'heroicon-m-banknotes',
        };
    }

    public function formatDiscount(float $value): string
    {
        return match ($this) {
            self::Percentage => number_format($value, 0).'% Off',
            self::Fixed => '৳ '.number_format($value, 2).' Off',
        };
    }
}
