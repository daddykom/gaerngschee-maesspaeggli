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
}
