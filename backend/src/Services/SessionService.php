<?php

declare(strict_types=1);

namespace App\Services;

final class SessionService
{
    private const USER_ID_KEY = 'user_id';

    public function setUserId(string $userId): void
    {
        $this->ensureSession();
        session_regenerate_id(true);
        $_SESSION[self::USER_ID_KEY] = $userId;
    }

    public function getUserId(): ?string
    {
        $this->ensureSession();
        $userId = $_SESSION[self::USER_ID_KEY] ?? null;

        return is_string($userId) && $userId !== '' ? $userId : null;
    }

    public function clear(): void
    {
        $this->ensureSession();
        $_SESSION = [];
        session_destroy();
    }

    private function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
