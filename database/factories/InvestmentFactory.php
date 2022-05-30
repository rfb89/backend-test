<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Investment>
 */
class InvestmentFactory extends Factory
{
    /**
     * Define the model's default state.
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'date'   => $this->faker->unique()->datetimeBetween('-1 year', '-1 week'),
            'amount' => $this->faker->randomNumber(5, true),
        ];
    }
}
