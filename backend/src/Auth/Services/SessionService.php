<?php

declare(strict_types=1);

namespace App\Auth\Services;

final class SessionService
{
    private const USER_ID_KEY = 'user_id';
    private const GROUP_KEY = 'group';
    private const FAIRGATE_USER_EXISTS_KEY = 'fairgate_user_exists';

    public function setUserId(string $userId): void
    {
        $this->ensureSession();
        session_regenerate_id(true);
        $_SESSION[self::USER_ID_KEY] = $userId;
    }

    public function setUser(string $userId, string $group, ?bool $fairgateUserExists = null): void
    {
        $this->ensureSession();
        session_regenerate_id(true);
        $_SESSION[self::USER_ID_KEY] = $userId;
        $_SESSION[self::GROUP_KEY] = $group;
        if ($fairgateUserExists !== null) {
            $_SESSION[self::FAIRGATE_USER_EXISTS_KEY] = $fairgateUserExists;
        } else {
            unset($_SESSION[self::FAIRGATE_USER_EXISTS_KEY]);
        }
    }

    public function getUserId(): ?string
    {
        $this->ensureSession();
        $userId = $_SESSION[self::USER_ID_KEY] ?? null;

        return is_string($userId) && $userId !== '' ? $userId : null;
    }

    public function getGroup(): ?string
    {
        $this->ensureSession();
        $group = $_SESSION[self::GROUP_KEY] ?? null;

        return is_string($group) && $group !== '' ? $group : null;
    }

    public function getFairgateUserExists(): ?bool
    {
        $this->ensureSession();
        $value = $_SESSION[self::FAIRGATE_USER_EXISTS_KEY] ?? null;

        return is_bool($value) ? $value : null;
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
