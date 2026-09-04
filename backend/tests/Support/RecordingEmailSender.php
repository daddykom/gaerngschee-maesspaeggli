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
}
