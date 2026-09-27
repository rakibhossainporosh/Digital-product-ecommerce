<?php

namespace Database\Factories;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 100, 1500);
        $qty = fake()->numberBetween(1, 3);
        $subtotal = round($unitPrice * $qty, 2);
        $costPrice = round($unitPrice * 0.7, 2);

        return [
            'order_number' => Order::generateOrderNumber(false),
            'customer_id' => Customer::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'discount_amount' => 0.00,
            'total_amount' => $subtotal,
            'cost_price' => $costPrice,
            'payment_method' => PaymentMethod::Wallet,
            'payment_status' => PaymentStatus::Paid,
            'fulfillment_status' => FulfillmentStatus::Fulfilled,
            'status' => OrderStatus::Completed,
            'wallet_amount_paid' => $subtotal,
            'gateway_amount_paid' => 0.00,
            'gateway_transaction_id' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'customer_notes' => null,
            'admin_notes' => null,
            'fulfilled_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Pending,
            'fulfillment_status' => FulfillmentStatus::Unfulfilled,
            'status' => OrderStatus::Pending,
            'wallet_amount_paid' => 0.00,
            'fulfilled_at' => null,
        ]);
    }

    public function unfulfilled(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Paid,
            'fulfillment_status' => FulfillmentStatus::Unfulfilled,
            'status' => OrderStatus::Processing,
            'fulfilled_at' => null,
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => PaymentStatus::Refunded,
            'fulfillment_status' => FulfillmentStatus::RefundedToWallet,
            'status' => OrderStatus::Refunded,
        ]);
    }

    public function reseller(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_number' => Order::generateOrderNumber(true),
        ]);
    }
}
