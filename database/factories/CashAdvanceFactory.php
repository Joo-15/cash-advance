<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CashAdvance>
 */
class CashAdvanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(6, 6),
            'request_date' => $this->faker->date(),
            'purpose' => $this->faker->sentence(),
            'amount' => $this->faker->numberBetween(100000, 2000000),
            'status' => $this->faker->randomElement([
                'pending',
            ]),
        ];
    }
}
