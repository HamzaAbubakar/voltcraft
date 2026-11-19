<?php

namespace Database\Factories;

use App\Models\NotificationDevice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationDeviceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = NotificationDevice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fcm_token' => fake()->unique()->regexify('[A-Za-z0-9_-]{100,160}'),
            'device_name' => fake()->word(),
            'platform' => 'android',
            'last_used_at' => now(),
        ];
    }
}
