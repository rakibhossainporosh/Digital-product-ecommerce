<?php

namespace Database\Factories;

use App\Enums\PaymentTransactionStatus;
use App\Enums\PaymentTransactionType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentTransaction>
 */
class PaymentTransactionFactory extends Factory
{
    protected $model = PaymentTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => 'TRX-'.now()->format('Ymd').'-'.$this->faker->unique()->numerify('######'),
            'customer_id' => Customer::factory(),
            'order_id' => null,
            'type' => PaymentTransactionType::Deposit,
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'fee' => 0,
            'currency' => 'BDT',
            'gateway_name' => 'uddoktapay',
            'invoice_id' => null,
            'gateway_transaction_id' => null,
            'payment_channel' => null,
            'status' => PaymentTransactionStatus::Pending,
            'payment_url' => null,
            'metadata' => null,
            'raw_payload' => null,
            'paid_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentTransactionStatus::Completed,
            'invoice_id' => $this->faker->uuid(),
            'gateway_transaction_id' => $this->faker->regexify('[A-Z0-9]{10}'),
            'payment_channel' => $this->faker->randomElement(['bkash', 'nagad', 'rocket']),
            'paid_at' => now(),
        ]);
    }

    public function orderPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PaymentTransactionType::OrderPayment,
            'order_id' => Order::factory(),
        ]);
    }
}
