<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'referral_code' => 'REF'.strtoupper(Str::random(6)),
            'referred_by' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'whatsapp_number' => '+8801'.fake()->numberBetween(300000000, 999999999),
            'password' => static::$password ??= Hash::make('password'),
            'google_id' => null,
            'facebook_id' => null,
            'status' => CustomerStatus::Active,
            'balance' => 0.00,
            'is_reseller' => false,
            'reseller_discount' => null,
            'remember_token' => Str::random(10),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatus::Active,
        ]);
    }

    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatus::Banned,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatus::Suspended,
        ]);
    }

    public function reseller(float $discount = 10.00): static
    {
        return $this->state(fn (array $attributes) => [
            'is_reseller' => true,
            'reseller_discount' => $discount,
        ]);
    }

    public function withBalance(float $balance): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $balance,
        ]);
    }
}
