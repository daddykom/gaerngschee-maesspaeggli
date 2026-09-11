<?php

declare(strict_types=1);

namespace App\Auth\Services;

use App\Auth\Data\PasswordResetTokenRepository;
use App\Shared\Database\Database;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use Throwable;

final class PasswordResetTokenService
{
    private readonly PDO $pdo;
    private readonly PasswordResetTokenRepository $tokens;

    public function __construct(?PDO $pdo = null, ?PasswordResetTokenRepository $tokens = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
        $this->tokens = $tokens ?? new PasswordResetTokenRepository($this->pdo);
    }

    /** @return array{token: string, expiresAt: string} */
    public function issue(string $userId, ?DateTimeImmutable $now = null): array
    {
        $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $expiresAt = $now->modify('+10 minutes');

        $this->pdo->beginTransaction();
        try {
            $this->tokens->invalidateActiveForUser($userId, $now);
            $this->tokens->create($userId, hash('sha256', $token), $expiresAt);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return ['token' => $token, 'expiresAt' => $expiresAt->format(DATE_ATOM)];
    }

    public function consume(string $token, ?DateTimeImmutable $now = null): ?string
    {
        $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $this->pdo->beginTransaction();

        try {
            $record = $this->tokens->findValid(hash('sha256', $token), $now);
            if ($record === null || !$this->tokens->consume($record['id'], $now)) {
                $this->pdo->rollBack();
                return null;
            }
            $this->pdo->commit();

            return $record['user_id'];
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
