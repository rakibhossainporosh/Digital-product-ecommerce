<?php

namespace App\Filament\Resources\LoginHistories\Pages;

use App\Filament\Resources\LoginHistories\LoginHistoryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewLoginHistory extends ViewRecord
{
    protected static string $resource = LoginHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
