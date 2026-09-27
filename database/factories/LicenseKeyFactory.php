<?php

namespace Database\Factories;

use App\Enums\LicenseKeyStatus;
use App\Models\LicenseKey;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LicenseKey>
 */
class LicenseKeyFactory extends Factory
{
    protected $model = LicenseKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'key' => 'VIP-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)),
            'status' => LicenseKeyStatus::Available,
            'order_id' => null,
            'order_item_id' => null,
            'sold_at' => null,
            'batch_ref' => 'BATCH-'.date('Ymd').'-'.fake()->numerify('###'),
            'notes' => fake()->optional(0.3)->sentence(),
            'created_by' => null,
        ];
    }

    /**
     * Indicate that the key is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseKeyStatus::Available,
            'order_id' => null,
            'sold_at' => null,
        ]);
    }

    /**
     * Indicate that the key is sold.
     */
    public function sold(?int $orderId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseKeyStatus::Sold,
            'order_id' => $orderId ?? fake()->numberBetween(1001, 9999),
            'order_item_id' => fake()->numberBetween(1, 100),
            'sold_at' => now()->subMinutes(fake()->numberBetween(5, 1440)),
        ]);
    }

    /**
     * Indicate that the key is reserved.
     */
    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseKeyStatus::Reserved,
        ]);
    }

    /**
     * Indicate that the key is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LicenseKeyStatus::Revoked,
            'notes' => 'Revoked by admin due to supplier invalidation',
        ]);
    }
}
