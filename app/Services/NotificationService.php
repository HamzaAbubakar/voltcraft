<?php

namespace App\Services;

use App\Models\NotificationRecipient;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Get the list of notification email addresses.
     *
     * Reads from the notification_recipients table managed via the Control Panel.
     * Falls back to the NOTIFICATION_EMAILS environment variable if no rows exist.
     *
     * @return array<string>
     */
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

    /**
     * Send a low-stock alert when an inventory item falls to/below its reorder point.
     */
    public function lowStockAlert(string $itemSku, string $itemName, float $quantity, float $reorderPoint): void
    {
        $recipients = $this->recipients();

        if ($recipients === []) {
            return;
        }

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

    /**
     * Send an investment due alert when investments are due or overdue.
     *
     * @param  array<int, array{investor_name: string, maturity_date: string, days_until_maturity: int}>  $investments
     */
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
    }
}
