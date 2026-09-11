<?php

declare(strict_types=1);

namespace App\Auth\Data;

use DateTimeImmutable;
use PDO;

final class PasswordResetTokenRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function invalidateActiveForUser(string $userId, DateTimeImmutable $now): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE password_reset_tokens
             SET used_at = :used_at, updated_at = :updated_at
             WHERE user_id = :user_id AND used_at IS NULL AND expires_at > :now',
        );
        $timestamp = $now->format('Y-m-d H:i:s');
        $statement->execute([
            'user_id' => $userId,
            'used_at' => $timestamp,
            'updated_at' => $timestamp,
            'now' => $timestamp,
        ]);
    }

    public function create(string $userId, string $tokenHash, DateTimeImmutable $expiresAt): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO password_reset_tokens (id, user_id, token_hash, expires_at)
             VALUES (:id, :user_id, :token_hash, :expires_at)',
        );
        $statement->execute([
            'id' => $this->createUuid(),
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    /** @return array{id: string, user_id: string}|null */
    public function findValid(string $tokenHash, DateTimeImmutable $now): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, user_id FROM password_reset_tokens
             WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > :now',
        );
        $statement->execute([
            'token_hash' => $tokenHash,
            'now' => $now->format('Y-m-d H:i:s'),
        ]);
        $row = $statement->fetch();

        return $row ?: null;
    }

    public function consume(string $id, DateTimeImmutable $now): bool
    {
        $timestamp = $now->format('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            'UPDATE password_reset_tokens
             SET used_at = :used_at, updated_at = :updated_at
             WHERE id = :id AND used_at IS NULL AND expires_at > :now',
        );
        $statement->execute([
            'id' => $id,
            'used_at' => $timestamp,
            'updated_at' => $timestamp,
            'now' => $timestamp,
        ]);

        return $statement->rowCount() === 1;
    }

    private function createUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
