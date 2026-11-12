<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectExpense>
 */
class ProjectExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'employee_id' => Employee::factory(),
            'recorded_by_user_id' => User::factory(),
            'category' => fake()->randomElement(['food', 'accommodation', 'travel', 'other']),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'expense_date' => fake()->date(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
