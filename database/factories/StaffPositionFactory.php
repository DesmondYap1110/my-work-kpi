<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StaffPosition>
 */
class StaffPositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'position_name' => fake()->unique()->jobTitle(),
            'job_scope' => fake()->paragraph(),
            'kpistatus' => false,
        ];
    }
}
