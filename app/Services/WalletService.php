<?php

namespace App\Services;

use App\Enums\CustomerStatus;
use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Exceptions\CustomerAccountLockedException;
use App\Exceptions\InsufficientWalletBalanceException;
use App\Models\Customer;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WalletService
{
    /**
     * Credit the customer's wallet balance (Atomic & Thread-Safe).
     *
     * @param  array<string, mixed>  $metadata
     *
     * @throws CustomerAccountLockedException
     * @throws InvalidArgumentException
     */
    public function credit(
        Customer $customer,
        float $amount,
        WalletTransactionType $type,
        string $description,
        ?string $referenceId = null,
        ?User $admin = null,
        array $metadata = []
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Credit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($customer, $amount, $type, $description, $referenceId, $admin, $metadata): WalletTransaction {
            /** @var Customer $lockedCustomer */
            $lockedCustomer = Customer::where('id', $customer->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedCustomer->status === CustomerStatus::Banned) {
                throw new CustomerAccountLockedException($lockedCustomer->status, 'Cannot credit a banned customer account.');
            }

            $openingBalance = (float) $lockedCustomer->balance;
            $closingBalance = round($openingBalance + $amount, 2);

            $lockedCustomer->forceFill([
                'balance' => $closingBalance,
            ])->save();

            $transaction = WalletTransaction::create([
                'customer_id' => $lockedCustomer->id,
                'type' => $type,
                'direction' => TransactionDirection::Credit,
                'amount' => $amount,
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'description' => $description,
                'reference_id' => $referenceId,
                'admin_id' => $admin?->id,
                'metadata' => empty($metadata) ? null : $metadata,
            ]);

            $customer->balance = $closingBalance;

            return $transaction;
        });
    }

    /**
     * Debit the customer's wallet balance (Atomic & Thread-Safe).
     *
     * @param  array<string, mixed>  $metadata
     *
     * @throws CustomerAccountLockedException
     * @throws InsufficientWalletBalanceException
     * @throws InvalidArgumentException
     */
    public function debit(
        Customer $customer,
        float $amount,
        WalletTransactionType $type,
        string $description,
        ?string $referenceId = null,
        ?User $admin = null,
        array $metadata = []
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Debit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($customer, $amount, $type, $description, $referenceId, $admin, $metadata): WalletTransaction {
            /** @var Customer $lockedCustomer */
            $lockedCustomer = Customer::where('id', $customer->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedCustomer->status === CustomerStatus::Banned) {
                throw new CustomerAccountLockedException($lockedCustomer->status, 'Cannot debit a banned customer account.');
            }

            $openingBalance = (float) $lockedCustomer->balance;

            if ($openingBalance < $amount) {
                throw new InsufficientWalletBalanceException(
                    currentBalance: $openingBalance,
                    requiredAmount: $amount,
                    message: "Customer balance (৳ {$openingBalance}) is insufficient for debit of ৳ {$amount}."
                );
            }

            $closingBalance = round($openingBalance - $amount, 2);

            $lockedCustomer->forceFill([
                'balance' => $closingBalance,
            ])->save();

            $transaction = WalletTransaction::create([
                'customer_id' => $lockedCustomer->id,
                'type' => $type,
                'direction' => TransactionDirection::Debit,
                'amount' => $amount,
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'description' => $description,
                'reference_id' => $referenceId,
                'admin_id' => $admin?->id,
                'metadata' => empty($metadata) ? null : $metadata,
            ]);

            $customer->balance = $closingBalance;

            return $transaction;
        });
    }

    /**
     * Admin balance manual adjustment (Credit or Debit) with audit recording.
     */
    public function adminAdjust(
        Customer $customer,
        float $amount,
        TransactionDirection $direction,
        string $reason,
        User $admin
    ): WalletTransaction {
        $referenceId = 'ADM-'.strtoupper(bin2hex(random_bytes(4)));

        if ($direction === TransactionDirection::Credit) {
            return $this->credit(
                customer: $customer,
                amount: $amount,
                type: WalletTransactionType::AdminAdjust,
                description: "Admin Credit: {$reason}",
                referenceId: $referenceId,
                admin: $admin,
                metadata: ['reason' => $reason, 'adjusted_by' => $admin->name]
            );
        }

        return $this->debit(
            customer: $customer,
            amount: $amount,
            type: WalletTransactionType::AdminAdjust,
            description: "Admin Debit: {$reason}",
            referenceId: $referenceId,
            admin: $admin,
            metadata: ['reason' => $reason, 'adjusted_by' => $admin->name]
        );
    }

    /**
     * Refund money back to the customer's wallet.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function refund(
        Customer $customer,
        float $amount,
        string $description,
        ?string $referenceId = null,
        array $metadata = []
    ): WalletTransaction {
        return $this->credit(
            customer: $customer,
            amount: $amount,
            type: WalletTransactionType::Refund,
            description: $description,
            referenceId: $referenceId,
            metadata: $metadata
        );
    }

    /**
     * Audit and verify the integrity of a customer's balance against their transaction ledger.
     *
     * @return array{is_valid: bool, current_balance: float, calculated_balance: float, difference: float}
     */
    public function verifyLedgerIntegrity(Customer $customer): array
    {
        $totalCredits = (float) WalletTransaction::where('customer_id', $customer->id)
            ->where('direction', TransactionDirection::Credit)
            ->sum('amount');

        $totalDebits = (float) WalletTransaction::where('customer_id', $customer->id)
            ->where('direction', TransactionDirection::Debit)
            ->sum('amount');

        $calculatedBalance = round($totalCredits - $totalDebits, 2);
        $currentBalance = round((float) $customer->balance, 2);
        $difference = round(abs($calculatedBalance - $currentBalance), 2);

        return [
            'is_valid' => $difference < 0.01,
            'current_balance' => $currentBalance,
            'calculated_balance' => $calculatedBalance,
            'difference' => $difference,
        ];
    }
}
