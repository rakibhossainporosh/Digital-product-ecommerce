<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Widgets\CustomerStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Url;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    #[Url]
    public ?string $filter = null;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Customer')
                ->icon('heroicon-m-user-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerStatsOverview::class,
        ];
    }
}
