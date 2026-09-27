<?php

namespace App\Filament\Resources\PromoCodes\Pages;

use App\Filament\Resources\PromoCodes\PromoCodeResource;
use App\Filament\Resources\PromoCodes\Widgets\PromoCodeStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Url;

class ListPromoCodes extends ListRecords
{
    protected static string $resource = PromoCodeResource::class;

    #[Url]
    public ?string $filter = null;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Promo Code')
                ->icon('heroicon-m-ticket'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PromoCodeStatsOverview::class,
        ];
    }
}
