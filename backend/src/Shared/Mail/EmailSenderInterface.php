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

    public function sendOrderStatus(string $recipient, string $status, string $locale = 'de'): void;

    public function sendUserCreated(string $recipient, string $temporaryPassword): void;

    public function sendUserEmailChanged(string $recipient): void;

    public function sendPasswordReset(string $recipient, string $resetUrl): void;

    /** @param array<string, mixed> $order */
    public function sendOrderConfirmation(string $recipient, array $order): void;

    /** @param array<string, mixed> $order */
    public function renderOrderConfirmation(array $order, string $mailStatus): array;

    /** @param array<string, mixed> $order */
    public function renderDeliveryNotification(array $order, string $deliveryUrl, string $qrDataUri): array;

    public function sendStoredEmail(string $recipient, string $subject, string $html, string $text): void;
}
