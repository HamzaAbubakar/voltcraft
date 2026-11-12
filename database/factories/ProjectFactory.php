<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Solar Installation',
            'customer_name' => fake()->name(),
            'site_location' => fake()->city(),
            'system_size_watts' => fake()->numberBetween(1000, 100000),
            'customer_rate_per_watt' => fake()->randomFloat(2, 4, 8),
            'outsourced_rate_per_watt' => fake()->randomFloat(2, 3, 6),
            'status' => 'active',
            'start_date' => fake()->date(),
        ];
    }
}
