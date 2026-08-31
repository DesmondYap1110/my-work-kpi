<?php

namespace Database\Factories;

use App\Models\StaffPosition;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'staff_name' => fake()->name(),
            'staffimg' => 'default.jpg',
            'gender' => fake()->randomElement(['Male', 'Female']),
            'ic' => fake()->numerify('##########'),
            'dob' => fake()->date(),
            'contact' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'staff_address' => fake()->address(),
            'postcode' => fake()->postcode(),
            'city' => fake()->city(),
            'states' => fake()->state(),
            'datejointeam' => fake()->date(),
            'datejoincompany' => fake()->date(),
            'password' => static::$password ??= Hash::make('password'),
            'staffstatus' => true,
            'position_id' => StaffPosition::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
