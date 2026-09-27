<?php

namespace App\Filament\Resources\PaymentTransactions\Widgets;

use App\Enums\PaymentTransactionStatus;
use App\Models\PaymentTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentTransactionStats extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();

        $totalVolume = PaymentTransaction::where('status', PaymentTransactionStatus::Completed)
            ->sum('amount');

        $totalFees = PaymentTransaction::where('status', PaymentTransactionStatus::Completed)
            ->sum('fee');

        $todaySuccess = PaymentTransaction::where('status', PaymentTransactionStatus::Completed)
            ->where('paid_at', '>=', $today)
            ->count();

        $pendingPayments = PaymentTransaction::where('status', PaymentTransactionStatus::Pending)
            ->count();

        return [
            Stat::make('Total Gateway Volume', '৳ '.number_format($totalVolume, 2))
                ->description('All successful online payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Gateway Fees', '৳ '.number_format($totalFees, 2))
                ->description('Gateway charges deducted')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Today\'s Successful', $todaySuccess)
                ->description('Completed transactions today')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pending / Incomplete', $pendingPayments)
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
