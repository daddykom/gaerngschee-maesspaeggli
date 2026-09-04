<?php

declare(strict_types=1);

namespace App\Registration\Data;

use DateTimeImmutable;
use PDO;

final class RegistrationTokenRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function invalidateActiveForEmail(string $email, DateTimeImmutable $now): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE registration_tokens
             SET used_at = :used_at, updated_at = :updated_at
             WHERE email = :email
               AND used_at IS NULL
               AND expires_at > :now',
        );
        $timestamp = $now->format('Y-m-d H:i:s');
        $stmt->execute([
            'email' => $email,
            'used_at' => $timestamp,
            'updated_at' => $timestamp,
            'now' => $timestamp,
        ]);
    }

    public function create(string $email, string $tokenHash, DateTimeImmutable $expiresAt): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO registration_tokens
                (id, email, token_hash, expires_at)
             VALUES (:id, :email, :token_hash, :expires_at)',
        );
        $stmt->execute([
            'id' => $this->createUuid(),
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function findValid(string $tokenHash, DateTimeImmutable $now): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, email, token_hash, expires_at, used_at
             FROM registration_tokens
             WHERE token_hash = :token_hash
               AND used_at IS NULL
               AND expires_at > :now',
        );
        $stmt->execute([
            'token_hash' => $tokenHash,
            'now' => $now->format('Y-m-d H:i:s'),
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function consume(string $id, DateTimeImmutable $now): bool
    {
        $timestamp = $now->format('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            'UPDATE registration_tokens
             SET used_at = :used_at, updated_at = :updated_at
             WHERE id = :id
               AND used_at IS NULL
               AND expires_at > :now',
        );
        $stmt->execute([
            'id' => $id,
            'used_at' => $timestamp,
            'updated_at' => $timestamp,
            'now' => $timestamp,
        ]);

        return $stmt->rowCount() === 1;
    }

    public function deleteExpiredBefore(DateTimeImmutable $cutoff): int
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM registration_tokens WHERE expires_at <= :cutoff',
        );
        $statement->execute(['cutoff' => $cutoff->format('Y-m-d H:i:s')]);

        return $statement->rowCount();
    }

    private function createUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
