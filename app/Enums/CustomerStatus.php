<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CustomerStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Banned = 'banned';
    case Suspended = 'suspended';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Banned => 'Banned',
            self::Suspended => 'Suspended',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Banned => 'danger',
            self::Suspended => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-m-check-circle',
            self::Banned => 'heroicon-m-x-circle',
            self::Suspended => 'heroicon-m-pause-circle',
        };
    }
}
