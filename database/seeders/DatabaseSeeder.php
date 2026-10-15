<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = config('app.seed_admin_password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('Set SEED_ADMIN_PASSWORD in .env before running the database seeder.');
        }

        User::updateOrCreate(
            ['email' => config('app.seed_admin_email')],
            [
                'name' => config('app.seed_admin_name'),
                'password' => $password,
            ],
        );
    }
}
