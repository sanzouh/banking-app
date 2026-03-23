<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'withdraw_num' => fake()->unique()->numerify('#####'),
            'check_num' => fake()->unique()->numerify('#####'),
            'account_num' => \App\Models\Client::factory(),
            'amount' => fake()->numberBetween(1000, 10000000),
            'user_id' => \App\Models\User::factory()
        ];
    }
}
