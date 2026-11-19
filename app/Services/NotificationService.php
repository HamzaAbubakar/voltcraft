<?php

namespace App\Services;

use App\Models\NotificationRecipient;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function recipients(): array
    {
        $dbRecipients = NotificationRecipient::query()->pluck('email')->toArray();

        if ($dbRecipients !== []) {
            return array_unique(array_map('trim', $dbRecipients));
        }

        $raw = (string) env('NOTIFICATION_EMAILS');

        if ($raw === '') {
            return [];
        }

        return array_unique(array_map(
            fn (string $email): string => trim($email),
            explode(',', $raw)
        ));
    }

    public function lowStockAlert(string $itemSku, string $itemName, float $quantity, float $reorderPoint): void
    {
        $recipients = $this->recipients();

        if ($recipients !== []) {
            Mail::send(
                'emails.low-stock',
                [
                    'itemSku' => $itemSku,
                    'itemName' => $itemName,
                    'quantity' => number_format($quantity, 3),
                    'reorderPoint' => number_format($reorderPoint, 3),
                ],
                fn ($message) => $message
                    ->to($recipients)
                    ->subject('Voltcraft ERP: Low stock alert for '.$itemName)
            );
        }

        $this->sendPushToAdmins(
            'Voltcraft ERP: Low stock',
            "{$itemName} (SKU: {$itemSku}) is at or below its reorder point.",
            [
                'type' => 'low_stock',
                'item_sku' => $itemSku,
                'item_name' => $itemName,
                'url' => route('inventory.index', [], false),
            ]
        );
    }

    public function investmentDueAlert(array $investments): void
    {
        $recipients = $this->recipients();

        if ($recipients === [] || $investments === []) {
            return;
        }

        Mail::send(
            'emails.investments-due',
            ['investments' => $investments],
            fn ($message) => $message
                ->to($recipients)
                ->subject('Voltcraft ERP: '.count($investments).' investment(s) due or overdue')
        );

        $this->sendPushToAdmins(
            'Voltcraft ERP: Investment repayments need attention',
            count($investments).' investment(s) due within 3 days or past their return date.',
            [
                'type' => 'investment_due',
                'count' => count($investments),
                'url' => route('investments.index', [], false),
            ]
        );
    }

    public function dbBackupReminder(): void
    {
        $recipients = $this->recipients();

        if ($recipients !== []) {
            Mail::send(
                'emails.db-backup-reminder',
                [],
                fn ($message) => $message
                    ->to($recipients)
                    ->subject('Voltcraft ERP: Database backup reminder')
            );
        }

        $this->sendPushToAdmins(
            'Voltcraft ERP: Database backup reminder',
            'It has been 7 days or more since the last database backup was downloaded.',
            [
                'type' => 'db_backup',
                'url' => route('control-panel.download-db', [], false),
            ]
        );
    }

    private function sendPushToAdmins(string $title, string $body, array $data = []): void
    {
        $users = User::query()->where('is_admin', true)->get();

        foreach ($users as $user) {
            foreach ($user->notificationDevices()->platform('android')->get() as $device) {
                $this->sendPushToToken($device->fcm_token, $title, $body, $data);
                $device->update(['last_used_at' => now()]);
            }
        }
    }

    private function sendPushToToken(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $projectId = config('services.firebase.project_id');

            if (! is_string($projectId) || $projectId === '') {
                throw new \RuntimeException('FCM_PROJECT_ID is not configured.');
            }

            $response = Http::timeout(10)
                ->withToken($this->getFcmAccessToken(), 'Bearer')
                ->post(sprintf(
                    'https://fcm.googleapis.com/v1/projects/%s/messages:send',
                    $projectId
                ), [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => array_merge($data, [
                            'url' => $data['url'] ?? '/dashboard',
                        ]),
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'channel_id' => 'voltcraft_alerts',
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                Log::info('FCM push sent', ['status' => $response->status()]);

                return true;
            }

            Log::error('FCM push failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('FCM push exception', ['message' => $e->getMessage()]);

            return false;
        }
    }

    private function getFcmAccessToken(): string
    {
        $cacheKey = 'voltcraft_fcm_access_token';

        $token = cache()->get($cacheKey);
        if ($token) {
            return $token;
        }

        $serviceAccountPath = config('services.firebase.service_account_path');

        if (! is_string($serviceAccountPath) || ! is_file($serviceAccountPath) || ! is_readable($serviceAccountPath)) {
            throw new \RuntimeException('FCM service account file is not configured or is unreadable.');
        }

        $serviceAccountJson = file_get_contents($serviceAccountPath);

        if ($serviceAccountJson === false) {
            throw new \RuntimeException('FCM service account file could not be read.');
        }

        $serviceAccount = json_decode($serviceAccountJson, true, 512, JSON_THROW_ON_ERROR);

        if (
            ! is_array($serviceAccount)
            || ! is_string($serviceAccount['private_key'] ?? null)
            || $serviceAccount['private_key'] === ''
            || ! is_string($serviceAccount['client_email'] ?? null)
            || $serviceAccount['client_email'] === ''
        ) {
            throw new \RuntimeException('FCM service account file is missing required fields.');
        }

        $projectId = config('services.firebase.project_id');

        if (
            ! is_string($projectId)
            || $projectId === ''
            || (isset($serviceAccount['project_id']) && $serviceAccount['project_id'] !== $projectId)
        ) {
            throw new \RuntimeException('FCM project ID does not match the service account.');
        }

        $issuedAt = time();
        $expiresAt = $issuedAt + 3600;

        $payload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ];

        $jwt = $this->signJwt(
            ['alg' => 'RS256', 'typ' => 'JWT'],
            $payload,
            $serviceAccount['private_key']
        );

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

        if (! $response->successful() || ! isset($response['access_token'])) {
            throw new \RuntimeException('Failed to obtain FCM access token: '.$response->body());
        }

        $accessToken = $response['access_token'];

        cache()->put($cacheKey, $accessToken, 55 * 60);

        return $accessToken;
    }

    private function signJwt(array $header, array $payload, string $privateKey): string
    {
        $headerBase64 = $this->base64UrlEncode(json_encode($header));
        $payloadBase64 = $this->base64UrlEncode(json_encode($payload));
        $signingInput = $headerBase64.'.'.$payloadBase64;

        $signature = '';
        $success = openssl_sign(
            $signingInput,
            $signature,
            $privateKey,
            OPENSSL_ALGO_SHA256
        );

        if (! $success || $signature === '') {
            throw new \RuntimeException('Failed to sign FCM JWT.');
        }

        return $signingInput.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
