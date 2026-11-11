<?php

namespace Database\Factories;

use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\ExpenseDescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cashAccount = CashAccount::factory();
        $description = ExpenseDescription::factory();

        return [
            'expense_description_id' => $description,
            'cash_transaction_id' => CashTransaction::factory()->cashOut()->for($cashAccount),
            'created_by_user_id' => User::factory(),
            'category' => fake()->randomElement(['Operations', 'Equipment', 'Personal support', 'Facilities']),
            'paid_for' => fake()->randomElement(['Company', fake()->name()]),
        ];
    }
}
