<?php

declare(strict_types=1);

namespace App\Services;

interface EmailSenderInterface
{
    public function sendAnmeldung(string $recipient, AnmeldungMailVariant $variant, string $locale = 'de'): void;
}
