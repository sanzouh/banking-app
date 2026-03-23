<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_num' => fake()->unique()->numerify('#####'),
            'name'    => fake()->name(),
            'balance' => fake()->randomFloat(2, 500, 500000),
        ];
    }
}
