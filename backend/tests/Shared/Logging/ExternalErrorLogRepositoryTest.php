<?php

declare(strict_types=1);

namespace Tests\Shared\Logging;

use App\Shared\Logging\ExternalErrorLogRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

final class ExternalErrorLogRepositoryTest extends TestCase
{
    public function testStoresExternalErrorDetails(): void
    {
        $pdo = TestDatabase::create();
        $repository = new ExternalErrorLogRepository($pdo);

        $repository->create('fairgate', 'contact_lookup', 'FSA returned HTTP status 429.', '{"message":"Too many requests"}', 429);

        $error = $pdo->query('SELECT source, operation, message, details, http_status FROM external_error_logs')->fetch();

        self::assertSame('fairgate', $error['source']);
        self::assertSame('contact_lookup', $error['operation']);
        self::assertSame('FSA returned HTTP status 429.', $error['message']);
        self::assertSame('{"message":"Too many requests"}', $error['details']);
        self::assertSame(429, (int) $error['http_status']);
    }

    public function testPrunesOldestEntriesToConfiguredLimit(): void
    {
        $pdo = TestDatabase::create();
        $repository = new ExternalErrorLogRepository($pdo);
        putenv('EXTERNAL_ERROR_LOG_MAX_ENTRIES=2');

        try {
            foreach (['first', 'second', 'third'] as $index => $message) {
                $repository->create('email', 'smtp_send', $message);
                $pdo->prepare('UPDATE external_error_logs SET created_at = :created_at WHERE message = :message')
                    ->execute(['created_at' => sprintf('2026-09-25 10:0%d:00', $index), 'message' => $message]);
            }

            self::assertSame(1, $repository->pruneToConfiguredLimit());
            self::assertSame(['second', 'third'], $pdo->query(
                'SELECT message FROM external_error_logs ORDER BY created_at, id',
            )->fetchAll(PDO::FETCH_COLUMN));
        } finally {
            putenv('EXTERNAL_ERROR_LOG_MAX_ENTRIES');
        }
    }
}
