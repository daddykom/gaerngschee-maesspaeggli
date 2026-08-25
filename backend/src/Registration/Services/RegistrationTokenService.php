<?php

declare(strict_types=1);

namespace App\Registration\Services;

use App\Registration\Data\RegistrationTokenRepository;
use App\Shared\Database\Database;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use Throwable;

final class RegistrationTokenService
{
    private readonly PDO $pdo;
    private readonly RegistrationTokenRepository $tokens;

    public function __construct(?PDO $pdo = null, ?RegistrationTokenRepository $tokens = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
        $this->tokens = $tokens ?? new RegistrationTokenRepository($this->pdo);
    }

    /** @return array{token: string, expiresAt: string} */
    public function issue(string $email, ?DateTimeImmutable $now = null): array
    {
        $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $expiresAt = $now->modify('+10 minutes');

        $this->pdo->beginTransaction();
        try {
            $this->tokens->invalidateActiveForEmail($email, $now);
            $this->tokens->create($email, hash('sha256', $token), $expiresAt);
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

            return (string) $record['email'];
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
