<?php

namespace Tests\Feature;

use App\Models\NotificationDevice;
use App\Models\NotificationRecipient;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_control_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('control-panel.index'));

        $response->assertOk()
            ->assertSee('Control Panel')
            ->assertSee('Download DB Backup')
            ->assertSee('Push Notification Devices');
    }

    public function test_admin_can_view_registered_push_devices(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $device = NotificationDevice::factory()->create([
            'user_id' => $admin->getKey(),
            'device_name' => 'Pixel 7',
            'fcm_token' => 'test-fcm-token-1234567890abcdef',
            'last_used_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('control-panel.index'));

        $response->assertOk()
            ->assertSee('Push Notification Devices')
            ->assertSee('1')
            ->assertSee('Pixel 7')
            ->assertSee($admin->name)
            ->assertSee($admin->email)
            ->assertSee('Android')
            ->assertSee('test-fcm-tok…');
    }

    public function test_admin_can_view_apk_download_section(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('control-panel.index'));

        $response->assertOk()
            ->assertSee('Download Voltcraft App')
            ->assertSee('Android APK')
            ->assertSee('Install the Voltcraft mobile app');
    }

    public function test_non_admin_cannot_access_control_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('control-panel.index'))->assertForbidden();
    }

    public function test_admin_can_add_notification_recipient(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('control-panel.recipients.store'), [
            'email' => 'admin@example.com',
        ])->assertRedirect(route('control-panel.index'));

        $this->assertDatabaseHas('notification_recipients', [
            'email' => 'admin@example.com',
        ]);
    }

    public function test_admin_can_remove_notification_recipient(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $recipient = NotificationRecipient::factory()->create(['email' => 'remove@example.com']);

        $this->actingAs($admin)->delete(route('control-panel.recipients.destroy', $recipient))
            ->assertRedirect(route('control-panel.index'));

        $this->assertDatabaseMissing('notification_recipients', [
            'email' => 'remove@example.com',
        ]);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        NotificationRecipient::factory()->create(['email' => 'dup@example.com']);

        $response = $this->actingAs($admin)->post(route('control-panel.recipients.store'), [
            'email' => 'dup@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_db_backup_download_updates_timestamp(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('control-panel.download-db'));

        $this->assertDatabaseHas('system_settings', [
            'key' => 'last_db_backup_downloaded_at',
        ]);

        $this->assertNotNull(SystemSetting::get('last_db_backup_downloaded_at'));
    }

    public function test_guest_cannot_access_control_panel(): void
    {
        $this->get(route('control-panel.index'))->assertRedirect(route('login'));
    }
}
