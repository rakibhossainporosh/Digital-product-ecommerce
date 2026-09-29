<?php

namespace App\Filament\Resources\LoginHistories\Pages;

use App\Filament\Resources\LoginHistories\LoginHistoryResource;
use App\Filament\Resources\LoginHistories\Widgets\LoginHistoryStats;
use Filament\Resources\Pages\ListRecords;

class ListLoginHistories extends ListRecords
{
    protected static string $resource = LoginHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LoginHistoryStats::class,
        ];
    }
}
