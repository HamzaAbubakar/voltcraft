<?php

namespace Database\Factories;

use App\Models\NotificationRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationRecipientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = NotificationRecipient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
