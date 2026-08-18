<?php

declare(strict_types=1);

namespace App\Services;

interface FairgateContactProvider
{
    public function hasContactByEmail(string $email): bool;
}
