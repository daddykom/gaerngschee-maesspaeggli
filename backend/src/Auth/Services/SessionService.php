<?php

declare(strict_types=1);

namespace App\Auth\Services;

final class SessionService
{
    private const DEFAULT_IDLE_TIMEOUT = 3600;
    private const USER_ID_KEY = 'user_id';
    private const GROUP_KEY = 'group';
    private const FAIRGATE_USER_EXISTS_KEY = 'fairgate_user_exists';
    private const LAST_ACTIVITY_KEY = 'last_activity';

    public function setUserId(string $userId): void
    {
        $this->ensureSession();
        session_regenerate_id(true);
        $_SESSION[self::USER_ID_KEY] = $userId;
        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
    }

    public function setUser(string $userId, string $group, ?bool $fairgateUserExists = null): void
    {
        $this->ensureSession();
        session_regenerate_id(true);
        $_SESSION[self::USER_ID_KEY] = $userId;
        $_SESSION[self::GROUP_KEY] = $group;
        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
        if ($fairgateUserExists !== null) {
            $_SESSION[self::FAIRGATE_USER_EXISTS_KEY] = $fairgateUserExists;
        } else {
            unset($_SESSION[self::FAIRGATE_USER_EXISTS_KEY]);
        }
    }

    public function hasUserSession(): bool
    {
        $this->ensureSession();

        return isset($_SESSION[self::USER_ID_KEY]);
    }

    public function getUserId(): ?string
    {
        $this->ensureSession();
        if (!$this->isActive()) {
            return null;
        }

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

    public function touchActivity(): void
    {
        $this->ensureSession();
        if ($this->isActive()) {
            $_SESSION[self::LAST_ACTIVITY_KEY] = time();
        }
    }

    /** @return array{expiresAt: string, secondsRemaining: int}|null */
    public function getStatus(): ?array
    {
        $this->ensureSession();
        if (!$this->isActive()) {
            if ($this->hasUserSession()) {
                $this->clear();
            }

            return null;
        }

        $expiresAt = (int) $_SESSION[self::LAST_ACTIVITY_KEY] + $this->idleTimeout();

        return [
            'expiresAt' => gmdate(DATE_ATOM, $expiresAt),
            'secondsRemaining' => max(0, $expiresAt - time()),
        ];
    }

    public function idleTimeout(): int
    {
        $timeout = filter_var(getenv('SESSION_IDLE_TIMEOUT'), FILTER_VALIDATE_INT);

        return is_int($timeout) && $timeout > 0 ? $timeout : self::DEFAULT_IDLE_TIMEOUT;
    }

    private function isActive(): bool
    {
        $userId = $_SESSION[self::USER_ID_KEY] ?? null;
        if (!is_string($userId) || $userId === '') {
            return false;
        }

        $lastActivity = $_SESSION[self::LAST_ACTIVITY_KEY] ?? null;
        return is_int($lastActivity) && $lastActivity + $this->idleTimeout() > time();
    }

    private function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
