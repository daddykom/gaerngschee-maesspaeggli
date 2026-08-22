<?php

declare(strict_types=1);

namespace App\Data;

use DateTimeImmutable;
use PDO;

final class AccessKeyRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function invalidateActiveForUserAndPurpose(string $userId, string $purpose): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE user_access_keys
             SET used_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
             WHERE user_id = :user_id
               AND purpose = :purpose
               AND used_at IS NULL
               AND expires_at > CURRENT_TIMESTAMP',
        );
        $stmt->execute(['user_id' => $userId, 'purpose' => $purpose]);
    }

    public function create(
        string $userId,
        string $purpose,
        string $keyHash,
        DateTimeImmutable $expiresAt,
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO user_access_keys
                (id, user_id, purpose, key_hash, expires_at)
             VALUES (:id, :user_id, :purpose, :key_hash, :expires_at)',
        );
        $stmt->execute([
            'id' => $this->createUuid(),
            'user_id' => $userId,
            'purpose' => $purpose,
            'key_hash' => $keyHash,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);
    }

    public function findValid(string $keyHash, string $purpose, DateTimeImmutable $now): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, purpose, key_hash, expires_at, used_at
             FROM user_access_keys
             WHERE key_hash = :key_hash
               AND purpose = :purpose
               AND used_at IS NULL
               AND expires_at > :now',
        );
        $stmt->execute([
            'key_hash' => $keyHash,
            'purpose' => $purpose,
            'now' => $now->format('Y-m-d H:i:s'),
        ]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function consume(string $id, DateTimeImmutable $now): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE user_access_keys
             SET used_at = :used_at, updated_at = :updated_at
             WHERE id = :id
               AND used_at IS NULL
               AND expires_at > :now',
        );
        $stmt->execute([
            'id' => $id,
            'used_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
            'now' => $now->format('Y-m-d H:i:s'),
        ]);

        return $stmt->rowCount() === 1;
    }

    private function createUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
