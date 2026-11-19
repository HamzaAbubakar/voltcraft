<?php

namespace Tests\Feature;

use App\Models\NotificationDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushDeviceRegisterTest extends TestCase
{
    use RefreshDatabase;

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
        ])->assertRedirect()->assertSessionHas('success');

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
        ])->assertRedirect()->assertSessionHas('success');

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
        ])->assertSessionHasErrors('fcm_token');
    }
}
