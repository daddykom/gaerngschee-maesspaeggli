<?php

declare(strict_types=1);

namespace App\Services;

interface FairgateContactProvider
{
    /**
     * @return array{id: string, email: string, status: string|null}|null
     */
    public function findContactByEmail(string $email): ?array;
}
