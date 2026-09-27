<?php

namespace App\Enums;

enum LicenseKeyStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';
    case Revoked = 'revoked';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Reserved => 'Reserved',
            self::Sold => 'Sold',
            self::Revoked => 'Revoked',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Reserved => 'warning',
            self::Sold => 'info',
            self::Revoked => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Available => 'heroicon-m-check-circle',
            self::Reserved => 'heroicon-m-lock-closed',
            self::Sold => 'heroicon-m-shopping-bag',
            self::Revoked => 'heroicon-m-no-symbol',
        };
    }
}
