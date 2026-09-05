<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Registration\Services\AnmeldungMailVariant;
use App\Shared\Mail\EmailSenderInterface;

final class RecordingEmailSender implements EmailSenderInterface
{
    /** @var list<string> */
    public array $recipients = [];
    /** @var list<AnmeldungMailVariant> */
    public array $variants = [];
    public ?string $createdRecipient = null;
    public ?string $changedRecipient = null;
    /** @var list<array<string, mixed>> */
    public array $orderConfirmations = [];
    public bool $failOrderConfirmation = false;
    public bool $failStoredEmail = false;

    public function sendAnmeldung(string $recipient, AnmeldungMailVariant $variant, string $locale = 'de', ?string $loginUrl = null): void
    {
        $this->recipients[] = $recipient;
        $this->variants[] = $variant;
    }

    public function sendUserCreated(string $recipient, string $temporaryPassword): void
    {
        $this->createdRecipient = $recipient;
    }

    public function sendUserEmailChanged(string $recipient): void
    {
        $this->changedRecipient = $recipient;
    }

    public function sendOrderConfirmation(string $recipient, array $order): void
    {
        if ($this->failOrderConfirmation) {
            throw new \RuntimeException('SMTP failed');
        }

        $this->orderConfirmations[] = ['recipient' => $recipient, 'order' => $order];
    }

    public function renderOrderConfirmation(array $order): array
    {
        return ['subject' => 'subject', 'html' => json_encode($order, JSON_THROW_ON_ERROR), 'text' => 'text'];
    }

    public function renderDeliveryNotification(array $order, string $deliveryUrl, string $qrDataUri): array
    {
        return ['subject' => 'delivery', 'html' => $qrDataUri . $deliveryUrl, 'text' => $deliveryUrl];
    }

    public function sendStoredEmail(string $recipient, string $subject, string $html, string $text): void
    {
        if ($this->failOrderConfirmation || $this->failStoredEmail) {
            throw new \RuntimeException('SMTP failed');
        }
        $this->orderConfirmations[] = ['recipient' => $recipient, 'order' => ['subject' => $subject, 'html' => $html, 'text' => $text]];
    }
}
