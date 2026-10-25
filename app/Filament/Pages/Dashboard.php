<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LicenseKeys\LicenseKeyResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    protected ?string $heading = 'Store Performance Dashboard';

    protected ?string $subheading = 'Real-time sales metrics, digital inventory alerts, and customer activity.';

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('salesReport')
                ->label('Sales Report')
                ->icon('heroicon-m-chart-bar-square')
                ->color('gray')
                ->url(SalesReportPage::getUrl()),

            Action::make('manageKeys')
                ->label('License Keys')
                ->icon('heroicon-m-key')
                ->color('gray')
                ->url(LicenseKeyResource::getUrl('index')),

            Action::make('liveStore')
                ->label('View Storefront')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('primary')
                ->url('/')
                ->openUrlInNewTab(),
        ];
    }
}
