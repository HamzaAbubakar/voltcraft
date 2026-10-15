<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_the_configured_admin_user(): void
    {
        config([
            'app.seed_admin_name' => 'Voltcraft Administrator',
            'app.seed_admin_email' => 'admin@example.com',
            'app.seed_admin_password' => 'a-strong-test-password',
        ]);

        Artisan::call('db:seed');

        $user = User::where('email', 'admin@example.com')->sole();

        $this->assertSame('Voltcraft Administrator', $user->name);
        $this->assertTrue(Hash::check('a-strong-test-password', $user->password));
    }
}
