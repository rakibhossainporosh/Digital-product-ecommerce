<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TransactionDirection: string implements HasColor, HasIcon, HasLabel
{
    case Credit = 'credit';
    case Debit = 'debit';

    public function getLabel(): string
    {
        return match ($this) {
            self::Credit => 'Credit (+)',
            self::Debit => 'Debit (-)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Credit => 'success',
            self::Debit => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Credit => 'heroicon-m-plus-circle',
            self::Debit => 'heroicon-m-minus-circle',
        };
    }

    public function getPrefix(): string
    {
        return match ($this) {
            self::Credit => '+৳',
            self::Debit => '-৳',
        };
    }
}
