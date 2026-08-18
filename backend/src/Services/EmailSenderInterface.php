<?php

declare(strict_types=1);

namespace App\Services;

interface EmailSenderInterface
{
    public function sendProforma(string $recipient): void;
}
