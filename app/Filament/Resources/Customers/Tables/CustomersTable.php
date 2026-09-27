<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\CustomerStatus;
use App\Enums\TransactionDirection;
use App\Models\Customer;
use App\Models\User;
use App\Services\WalletService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->withCount('walletTransactions')->with('referrer');

                $filter = request()->query('filter');

                if ($filter === 'active') {
                    $query->where('status', CustomerStatus::Active);
                } elseif ($filter === 'resellers') {
                    $query->where('is_reseller', true);
                } elseif ($filter === 'banned') {
                    $query->where('status', CustomerStatus::Banned);
                } elseif ($filter === 'positive_balance') {
                    $query->where('balance', '>', 0);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('row_index')
                    ->rowIndex()
                    ->label('#')
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Customer Name')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->description(fn (Customer $record): ?string => $record->referral_code ? "Ref: {$record->referral_code}" : null),

                TextColumn::make('email')
                    ->label('Email & Contact')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->description(fn (Customer $record): ?string => $record->whatsapp_number ? "WA: {$record->whatsapp_number}" : null),

                TextColumn::make('balance')
                    ->label('Wallet Balance')
                    ->formatStateUsing(fn ($state): string => '৳ '.number_format((float) $state, 2))
                    ->weight(FontWeight::ExtraBold)
                    ->color('success')
                    ->icon('heroicon-m-wallet')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('is_reseller')
                    ->label('Tier')
                    ->badge()
                    ->formatStateUsing(fn (Customer $record): string => $record->is_reseller ? 'Reseller ('.($record->reseller_discount ?? 0).'%)' : 'Standard')
                    ->color(fn (Customer $record): string => $record->is_reseller ? 'warning' : 'gray')
                    ->sortable(),

                TextColumn::make('wallet_transactions_count')
                    ->label('Ledger')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->tooltip('Total transactions in wallet ledger'),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Account Status')
                    ->options(CustomerStatus::class)
                    ->native(false),

                TernaryFilter::make('is_reseller')
                    ->label('Reseller Tier')
                    ->placeholder('All Customers')
                    ->trueLabel('Resellers Only')
                    ->falseLabel('Regular Customers')
                    ->native(false),

                TernaryFilter::make('has_balance')
                    ->label('Wallet Balance')
                    ->placeholder('All Balances')
                    ->trueLabel('Positive Balance (> ৳ 0)')
                    ->falseLabel('Zero Balance (৳ 0)')
                    ->queries(
                        true: fn (Builder $query) => $query->where('balance', '>', 0),
                        false: fn (Builder $query) => $query->where('balance', '<=', 0),
                    )
                    ->native(false),

                TrashedFilter::make()->native(false),
            ])
            ->actions([
                Action::make('adjustBalance')
                    ->label('Adjust Balance')
                    ->icon('heroicon-m-banknotes')
                    ->color('warning')
                    ->modalWidth('lg')
                    ->modalHeading(fn (Customer $record): string => "Adjust Balance: {$record->name}")
                    ->modalDescription(fn (Customer $record): string => 'Current Balance: ৳ '.number_format((float) $record->balance, 2))
                    ->form([
                        Radio::make('direction')
                            ->label('Transaction Type')
                            ->options([
                                'credit' => 'Credit (+ Add Money to Customer Wallet)',
                                'debit' => 'Debit (- Deduct Money from Customer Wallet)',
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
                            ->placeholder('e.g. Verified manual bank transfer')
                            ->rows(3)
                            ->maxLength(255),
                    ])
                    ->action(function (Customer $record, array $data, WalletService $walletService): void {
                        $direction = TransactionDirection::from($data['direction']);
                        $amount = (float) $data['amount'];
                        $reason = (string) $data['reason'];
                        /** @var User $admin */
                        $admin = Auth::user();

                        try {
                            $walletService->adminAdjust(
                                customer: $record,
                                amount: $amount,
                                direction: $direction,
                                reason: $reason,
                                admin: $admin
                            );

                            Notification::make()
                                ->title('Wallet Balance Adjusted')
                                ->body("{$record->name}'s balance updated. New Balance: ৳ ".number_format((float) $record->balance, 2))
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

                ActionGroup::make([
                    Action::make('toggleReseller')
                        ->label('Manage Reseller')
                        ->icon('heroicon-m-sparkles')
                        ->color('info')
                        ->modalWidth('md')
                        ->fillForm(fn (Customer $record): array => [
                            'is_reseller' => $record->is_reseller,
                            'reseller_discount' => $record->reseller_discount,
                        ])
                        ->form([
                            Toggle::make('is_reseller')
                                ->label('Reseller Tier Status')
                                ->helperText('Enable or disable wholesale pricing privileges.')
                                ->reactive(),

                            TextInput::make('reseller_discount')
                                ->label('Global Reseller Discount (%)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%')
                                ->placeholder('e.g. 10.00'),
                        ])
                        ->action(function (Customer $record, array $data): void {
                            $record->update([
                                'is_reseller' => (bool) $data['is_reseller'],
                                'reseller_discount' => $data['reseller_discount'] ?? null,
                            ]);

                            Notification::make()
                                ->title('Reseller Status Updated')
                                ->success()
                                ->send();
                        }),

                    Action::make('manageStatus')
                        ->label('Change Status')
                        ->icon('heroicon-m-shield-exclamation')
                        ->color('danger')
                        ->modalWidth('sm')
                        ->fillForm(fn (Customer $record): array => [
                            'status' => $record->status->value,
                        ])
                        ->form([
                            Select::make('status')
                                ->label('Account Status')
                                ->options(CustomerStatus::class)
                                ->required()
                                ->native(false),
                        ])
                        ->action(function (Customer $record, array $data): void {
                            $record->update([
                                'status' => $data['status'],
                            ]);

                            Notification::make()
                                ->title('Status Updated')
                                ->body("Account status is now {$record->status->getLabel()}")
                                ->success()
                                ->send();
                        }),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
