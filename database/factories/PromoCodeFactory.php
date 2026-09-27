<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PromoCode>
 */
class PromoCodeFactory extends Factory
{
    protected $model = PromoCode::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper('PROMO'.Str::random(5)),
            'description' => fake()->sentence(),
            'type' => DiscountType::Percentage,
            'value' => 15.00,
            'min_spend' => null,
            'max_discount' => null,
            'product_id' => null,
            'category_id' => null,
            'max_uses' => 100,
            'max_uses_per_customer' => 1,
            'used_count' => 0,
            'exclude_resellers' => true,
            'starts_at' => null,
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ];
    }

    public function percentage(float $val = 15.00): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DiscountType::Percentage,
            'value' => $val,
        ]);
    }

    public function fixed(float $val = 100.00): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DiscountType::Fixed,
            'value' => $val,
        ]);
    }

    public function withSpendLimits(float $minSpend, float $maxCap): static
    {
        return $this->state(fn (array $attributes) => [
            'min_spend' => $minSpend,
            'max_discount' => $maxCap,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->addDays(5),
        ]);
    }
}
