<?php

namespace Database\Factories;

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Models\Customer;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 50, 2000);
        $opening = fake()->randomFloat(2, 0, 5000);

        return [
            'customer_id' => Customer::factory(),
            'type' => WalletTransactionType::Deposit,
            'direction' => TransactionDirection::Credit,
            'amount' => $amount,
            'opening_balance' => $opening,
            'closing_balance' => $opening + $amount,
            'description' => 'Wallet balance deposit',
            'reference_id' => 'TXN-'.strtoupper(Str::random(10)),
            'admin_id' => null,
            'metadata' => null,
        ];
    }

    public function deposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletTransactionType::Deposit,
            'direction' => TransactionDirection::Credit,
            'description' => 'Online payment gateway deposit',
        ]);
    }

    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletTransactionType::Purchase,
            'direction' => TransactionDirection::Debit,
            'description' => 'Product license key purchase',
        ]);
    }

    public function refund(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletTransactionType::Refund,
            'direction' => TransactionDirection::Credit,
            'description' => 'Refund for out of stock item',
        ]);
    }

    public function adminAdjust(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => WalletTransactionType::AdminAdjust,
            'description' => 'Manual administrative balance adjustment',
        ]);
    }
}
