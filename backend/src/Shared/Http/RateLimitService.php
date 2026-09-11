<?php

declare(strict_types=1);

namespace App\Shared\Http;

use App\Shared\Database\Database;
use DateTimeImmutable;
use DateTimeZone;
use PDO;

final class RateLimitService
{
    public function __construct(private readonly ?PDO $database = null)
    {
    }

    /** Returns the number of seconds to wait, or null when the request is allowed. */
    public function consume(string $key, int $limit, int $windowSeconds, ?DateTimeImmutable $now = null): ?int
    {
        $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $timestamp = $now->getTimestamp();
        $bucketTimestamp = intdiv($timestamp, $windowSeconds) * $windowSeconds;
        $bucketStart = (new DateTimeImmutable('@' . $bucketTimestamp))->setTimezone(new DateTimeZone('UTC'));
        $expiresAt = $bucketStart->modify('+' . $windowSeconds . ' seconds');
        $hash = hash_hmac('sha256', $key, getenv('APP_KEY') ?: (getenv('JWT_SECRET') ?: 'rate-limit-key'));
        $pdo = $this->database ?? Database::getConnection();

        $pdo->prepare('DELETE FROM rate_limits WHERE expires_at <= :now')->execute([
            'now' => $now->format('Y-m-d H:i:s'),
        ]);

        $pdo->beginTransaction();
        try {
            if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
                $select = $pdo->prepare(
                    'SELECT request_count FROM rate_limits WHERE key_hash = :key_hash AND bucket_start = :bucket_start',
                );
            } else {
                $select = $pdo->prepare(
                    'SELECT request_count FROM rate_limits WHERE key_hash = :key_hash AND bucket_start = :bucket_start FOR UPDATE',
                );
            }
            $select->execute([
                'key_hash' => $hash,
                'bucket_start' => $bucketStart->format('Y-m-d H:i:s'),
            ]);
            $current = $select->fetchColumn();

            if ($current === false) {
                $pdo->prepare(
                    'INSERT INTO rate_limits (key_hash, bucket_start, request_count, expires_at)
                     VALUES (:key_hash, :bucket_start, 1, :expires_at)',
                )->execute([
                    'key_hash' => $hash,
                    'bucket_start' => $bucketStart->format('Y-m-d H:i:s'),
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                ]);
                $count = 1;
            } else {
                $pdo->prepare(
                    'UPDATE rate_limits SET request_count = request_count + 1
                     WHERE key_hash = :key_hash AND bucket_start = :bucket_start',
                )->execute([
                    'key_hash' => $hash,
                    'bucket_start' => $bucketStart->format('Y-m-d H:i:s'),
                ]);
                $count = (int) $current + 1;
            }
            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }

        return $count > $limit ? max(1, $expiresAt->getTimestamp() - $timestamp) : null;
    }
}
