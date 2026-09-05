<?php

declare(strict_types=1);

namespace App\Shared\Mail;

use App\Registration\Services\AnmeldungMailVariant;
interface EmailSenderInterface
{
    public function sendAnmeldung(
        string $recipient,
        AnmeldungMailVariant $variant,
        string $locale = 'de',
        ?string $loginUrl = null,
    ): void;

    public function sendUserCreated(string $recipient, string $temporaryPassword): void;

    public function sendUserEmailChanged(string $recipient): void;

    /** @param array<string, mixed> $order */
    public function sendOrderConfirmation(string $recipient, array $order): void;

    /** @param array<string, mixed> $order */
    public function renderOrderConfirmation(array $order): array;

    /** @param array<string, mixed> $order */
    public function renderDeliveryNotification(array $order, string $deliveryUrl, string $qrDataUri): array;

    public function sendStoredEmail(string $recipient, string $subject, string $html, string $text): void;
}
