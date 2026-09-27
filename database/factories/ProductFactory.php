<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'icon' => 'heroicon-o-cube',
            'image' => null,
            'demo_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'description' => fake()->paragraph(),
            'features' => [
                'Instant Key Delivery',
                '24/7 Priority Support',
                'Undetected & Safe',
                'Auto Updates Included',
            ],
            'status' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
