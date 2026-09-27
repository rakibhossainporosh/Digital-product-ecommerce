<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Models\Customer;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class WalletTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'walletTransactions';

    protected static ?string $title = 'Wallet Ledger & Transactions';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-banknotes';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('direction')
                    ->label('Direction')
                    ->badge(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->weight(FontWeight::Bold)
                    ->color(fn (WalletTransaction $record): string => $record->direction === TransactionDirection::Credit ? 'success' : 'danger')
                    ->formatStateUsing(fn (WalletTransaction $record): string => ($record->direction === TransactionDirection::Credit ? '+৳ ' : '-৳ ').number_format((float) $record->amount, 2))
                    ->sortable(),

                TextColumn::make('opening_balance')
                    ->label('Opening')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('closing_balance')
                    ->label('Closing')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->weight(FontWeight::SemiBold)
                    ->color('primary'),

                TextColumn::make('reference_id')
                    ->label('Reference')
                    ->copyable()
                    ->copyMessage('Reference ID copied!')
                    ->placeholder('N/A')
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (WalletTransaction $record): string => $record->description),

                TextColumn::make('admin.name')
                    ->label('Adjusted By')
                    ->placeholder('System / Gateway')
                    ->icon('heroicon-m-user-circle')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Transaction Type')
                    ->options(WalletTransactionType::class)
                    ->native(false),

                SelectFilter::make('direction')
                    ->label('Direction')
                    ->options(TransactionDirection::class)
                    ->native(false),
            ])
            ->headerActions([
                Action::make('adjustBalance')
                    ->label('Manual Balance Adjustment')
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->color('warning')
                    ->modalWidth('lg')
                    ->modalHeading(fn (): string => 'Adjust Wallet Balance: '.$this->getOwnerRecord()->name)
                    ->modalDescription(fn (): string => 'Current Balance: ৳ '.number_format((float) $this->getOwnerRecord()->balance, 2))
                    ->form([
                        Radio::make('direction')
                            ->label('Adjustment Type')
                            ->options([
                                'credit' => 'Credit (+ Add Money to Wallet)',
                                'debit' => 'Debit (- Deduct Money from Wallet)',
                            ])
                            ->default('credit')
                            ->required(),

                        TextInput::make('amount')
                            ->label('Amount (৳)')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->prefix('৳')
                            ->placeholder('e.g. 500.00'),

                        Textarea::make('reason')
                            ->label('Audit Reason / Explanation')
                            ->required()
                            ->placeholder('e.g. Manual bank deposit verification #BK10294')
                            ->rows(3)
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, WalletService $walletService): void {
                        /** @var Customer $customer */
                        $customer = $this->getOwnerRecord();
                        $direction = TransactionDirection::from($data['direction']);
                        $amount = (float) $data['amount'];
                        $reason = (string) $data['reason'];
                        /** @var User $admin */
                        $admin = Auth::user();

                        try {
                            $walletService->adminAdjust(
                                customer: $customer,
                                amount: $amount,
                                direction: $direction,
                                reason: $reason,
                                admin: $admin
                            );

                            Notification::make()
                                ->title('Wallet Balance Adjusted')
                                ->body("Successfully adjusted {$customer->name}'s balance. New Balance: ৳ ".number_format((float) $customer->balance, 2))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Adjustment Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}
