<?php

namespace Database\Factories;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashTransaction>
 */
class CashTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cash_account_id' => CashAccount::factory(),
            'recorded_by_user_id' => User::factory(),
            'type' => 'cash_in',
            'amount' => fake()->randomFloat(2, 1, 100000),
            'description' => fake()->sentence(4),
            'note' => fake()->optional()->sentence(),
            'transaction_date' => fake()->date(),
        ];
    }

    public function cashOut(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'cash_out',
        ]);
    }
}
