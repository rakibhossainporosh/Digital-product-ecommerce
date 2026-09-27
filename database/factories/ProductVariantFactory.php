<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tiers = [
            ['name' => '1 Day Pass', 'days' => 1, 'regular' => 150.00, 'offer' => 120.00, 'cost' => 80.00],
            ['name' => '7 Days VIP', 'days' => 7, 'regular' => 550.00, 'offer' => 450.00, 'cost' => 300.00],
            ['name' => '30 Days Pro', 'days' => 30, 'regular' => 1500.00, 'offer' => 1250.00, 'cost' => 850.00],
            ['name' => 'Lifetime Access', 'days' => 3650, 'regular' => 4500.00, 'offer' => 3800.00, 'cost' => 2500.00],
        ];

        $tier = fake()->randomElement($tiers);

        return [
            'product_id' => Product::factory(),
            'duration_name' => $tier['name'],
            'duration_days' => $tier['days'],
            'regular_price' => $tier['regular'],
            'offer_price' => $tier['offer'],
            'cost_price' => $tier['cost'],
            'api_provider_id' => null,
            'is_popular' => false,
        ];
    }

    /**
     * Indicate that the variant is marked popular.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_popular' => true,
        ]);
    }
}
