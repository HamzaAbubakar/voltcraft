<?php

namespace Tests\Feature;

use App\Models\NotificationDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushDeviceRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_check_push_device_status(): void
    {
        $this->post(route('push-devices.status'), [
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
        ])->assertStatus(401);
    }

    public function test_guest_cannot_register_push_device(): void
    {
        $this->post(route('push-devices.register'), [
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
        ])->assertStatus(401);
    }

    public function test_user_can_register_push_device(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('push-devices.register'), [
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
            'device_name' => 'Pixel 7',
        ])->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('notification_devices', [
            'user_id' => $user->getKey(),
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
            'platform' => 'android',
            'device_name' => 'Pixel 7',
        ]);
    }

    public function test_registering_same_token_updates_existing_record(): void
    {
        $user = User::factory()->create();
        NotificationDevice::factory()->create([
            'user_id' => $user->getKey(),
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
            'device_name' => 'Old device',
        ]);

        $this->actingAs($user)->post(route('push-devices.register'), [
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
            'device_name' => 'Pixel 7',
        ])->assertStatus(200)->assertJson(['success' => true]);

        // Still one record, but device_name updated.
        $this->assertCount(1, NotificationDevice::query()->where('user_id', $user->getKey())->get());
        $this->assertDatabaseHas('notification_devices', [
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
            'device_name' => 'Pixel 7',
        ]);
    }

    public function test_missing_fcm_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('push-devices.register'), [
            'fcm_token' => '',
        ])->assertStatus(422)->assertJsonValidationErrors('fcm_token');
    }

    public function test_push_device_status_reports_current_user_device_registration(): void
    {
        $user = User::factory()->create();
        NotificationDevice::factory()->create([
            'user_id' => $user->getKey(),
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
        ]);

        $this->actingAs($user)->post(route('push-devices.status'), [
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
        ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'registered' => true,
            ]);
    }

    public function test_push_device_status_does_not_report_another_users_device(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        NotificationDevice::factory()->create([
            'user_id' => $otherUser->getKey(),
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
        ]);

        $this->actingAs($user)->post(route('push-devices.status'), [
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
        ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'registered' => false,
            ]);
    }
}
