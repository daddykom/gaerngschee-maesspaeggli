<?php

declare(strict_types=1);

namespace App\Services;

final class FakeFairgateClient implements FairgateContactProvider
{
    public function hasContactByEmail(string $email): bool
    {
        $email = strtolower(trim($email));
        $localPart = strstr($email, '@', true);
        if ($localPart === false) {
            return false;
        }

        $plusPosition = strpos($localPart, '+');
        if ($plusPosition === false) {
            return false;
        }

        return str_contains(substr($localPart, $plusPosition + 1), 'fair');
    }
}
