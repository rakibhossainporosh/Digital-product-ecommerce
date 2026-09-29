<?php

namespace Database\Factories;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoginHistory>
 */
class LoginHistoryFactory extends Factory
{
    protected $model = LoginHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email' => fake()->safeEmail(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'device_type' => 'Desktop',
            'browser' => 'Chrome',
            'platform' => 'Windows 10/11',
            'status' => 'success',
            'failure_reason' => null,
            'login_at' => now(),
        ];
    }

    public function failed(?string $reason = 'Invalid credentials'): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }
}
