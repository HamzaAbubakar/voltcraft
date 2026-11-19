<?php

namespace Tests\Feature;

use App\Models\NotificationDevice;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_alert_sends_fcm_using_the_configured_service_account_file(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'test-fcm-access-token',
                'expires_in' => 3600,
            ]),
            'https://fcm.googleapis.com/v1/projects/voltcraft-erp/messages:send' => Http::response([
                'name' => 'projects/voltcraft-erp/messages/test-message',
            ]),
        ]);

        $keyOptions = [
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ];
        $exportOptions = [];
        $openSslConfigPath = dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'openssl.cnf';

        if (is_file($openSslConfigPath)) {
            $keyOptions['config'] = $openSslConfigPath;
            $exportOptions['config'] = $openSslConfigPath;
        }

        $privateKey = openssl_pkey_new($keyOptions);

        if ($privateKey === false) {
            $this->fail('Unable to generate a test signing key.');
        }

        if (! openssl_pkey_export($privateKey, $privateKeyPem, null, $exportOptions)) {
            $this->fail('Unable to export a test signing key.');
        }

        $serviceAccountPath = tempnam(sys_get_temp_dir(), 'voltcraft-fcm-');

        if ($serviceAccountPath === false) {
            $this->fail('Unable to create a temporary service-account file.');
        }

        try {
            $serviceAccount = [
                'type' => 'service_account',
                'project_id' => 'voltcraft-erp',
                'private_key' => $privateKeyPem,
                'client_email' => 'test-service-account@voltcraft-erp.iam.gserviceaccount.com',
            ];
            $serviceAccountJson = json_encode($serviceAccount, JSON_THROW_ON_ERROR);

            if (file_put_contents($serviceAccountPath, $serviceAccountJson) === false) {
                $this->fail('Unable to write the temporary service-account file.');
            }

            config([
                'services.firebase.project_id' => 'voltcraft-erp',
                'services.firebase.service_account_path' => $serviceAccountPath,
            ]);

            $admin = User::factory()->create(['is_admin' => true]);
            NotificationDevice::factory()->create([
                'user_id' => $admin->getKey(),
                'fcm_token' => 'test-fcm-token-1234567890abcdef',
            ]);

            app(NotificationService::class)->lowStockAlert('SKU-1', 'Test battery', 1, 2);

            Http::assertSent(fn (Request $request): bool => $request->url() === 'https://oauth2.googleapis.com/token'
                && $request->hasHeader('Content-Type', 'application/x-www-form-urlencoded')
                && $request->data()['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer');

            Http::assertSent(function (Request $request): bool {
                if ($request->url() !== 'https://fcm.googleapis.com/v1/projects/voltcraft-erp/messages:send') {
                    return false;
                }

                $message = $request->data()['message'] ?? [];

                return $request->hasHeader('Authorization', 'Bearer test-fcm-access-token')
                    && ($message['token'] ?? null) === 'test-fcm-token-1234567890abcdef'
                    && ($message['data']['url'] ?? null) === route('inventory.index', [], false)
                    && ! isset($message['android']['notification']['click_action']);
            });
        } finally {
            if (is_file($serviceAccountPath)) {
                unlink($serviceAccountPath);
            }
        }
    }
}
