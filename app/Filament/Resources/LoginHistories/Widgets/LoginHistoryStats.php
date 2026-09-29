<?php

namespace App\Filament\Resources\LoginHistories\Widgets;

use App\Models\LoginHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoginHistoryStats extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();

        $totalEvents = LoginHistory::count();
        $successfulToday = LoginHistory::where('status', 'success')
            ->where('login_at', '>=', $today)
            ->count();
        $failedAttempts = LoginHistory::where('status', 'failed')->count();
        $failedToday = LoginHistory::where('status', 'failed')
            ->where('login_at', '>=', $today)
            ->count();

        return [
            Stat::make('Total Audit Records', number_format($totalEvents))
                ->description('All recorded authentication events')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Today\'s Successful Logins', number_format($successfulToday))
                ->description('Authorized sessions today')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Failed Attempts (Today)', number_format($failedToday))
                ->description('Total Failed: '.number_format($failedAttempts))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($failedToday > 0 ? 'danger' : 'gray'),
        ];
    }
}
