<?php

declare(strict_types=1);

namespace Tests\Auth;

use App\Auth\Data\AccessKeyRepository;
use App\Users\Data\UserRepository;
use App\Auth\Services\AccessKeyService;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PHPUnit\Framework\TestCase;

final class AccessKeyServiceTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $users;
    private AccessKeyService $service;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->exec(
            'CREATE TABLE users (
                id TEXT PRIMARY KEY,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                "group" TEXT NOT NULL,
                required_password_reset INTEGER NOT NULL DEFAULT 0,
                created_at TEXT,
                updated_at TEXT
            )',
        );
        $this->pdo->exec(
            'CREATE TABLE user_access_keys (
                id TEXT PRIMARY KEY,
                user_id TEXT NOT NULL,
                purpose TEXT NOT NULL,
                key_hash TEXT NOT NULL UNIQUE,
                expires_at TEXT NOT NULL,
                used_at TEXT NULL,
                created_at TEXT,
                updated_at TEXT
            )',
        );

        $this->users = new UserRepository($this->pdo);
        $this->service = new AccessKeyService(
            $this->pdo,
            $this->users,
            new AccessKeyRepository($this->pdo),
        );
    }

    public function testGeneratedKeyIsHashedAndExpiresAfterTenMinutes(): void
    {
        $user = $this->users->createUser('client@example.com', 'secret', 'client');
        $now = new DateTimeImmutable('2026-08-22 12:00:00', new DateTimeZone('UTC'));

        $generated = $this->service->generateForUser($user['id'], AccessKeyService::CLIENT_LOGIN, $now);
        $stored = $this->pdo->query('SELECT key_hash, expires_at FROM user_access_keys')->fetch();

        self::assertIsArray($generated);
        self::assertNotSame($generated['accessKey'], $stored['key_hash']);
        self::assertSame(hash('sha256', $generated['accessKey']), $stored['key_hash']);
        self::assertSame('2026-08-22 12:10:00', $stored['expires_at']);
    }

    public function testPasswordResetConsumesKeyOnlyAfterSuccessfulPasswordUpdate(): void
    {
        $user = $this->users->createUser('client@example.com', 'old-secret', 'client');
        $now = new DateTimeImmutable('2026-08-22 12:00:00', new DateTimeZone('UTC'));
        $generated = $this->service->generateForUser($user['id'], AccessKeyService::PASSWORD_RESET, $now);

        self::assertNotNull($generated);
        self::assertNotNull($this->service->resetPassword($generated['accessKey'], 'new-secret', $now->modify('+1 minute')));
        self::assertNotNull($this->users->verifyPassword('client@example.com', 'new-secret'));
        self::assertNull($this->service->resetPassword($generated['accessKey'], 'another-secret', $now->modify('+2 minutes')));
    }

    public function testExpiredKeyCannotResetPassword(): void
    {
        $user = $this->users->createUser('client@example.com', 'old-secret', 'client');
        $createdAt = new DateTimeImmutable('2026-08-22 12:00:00', new DateTimeZone('UTC'));
        $generated = $this->service->generateForUser($user['id'], AccessKeyService::PASSWORD_RESET, $createdAt);

        self::assertNotNull($generated);
        self::assertNull($this->service->resetPassword($generated['accessKey'], 'new-secret', $createdAt->modify('+10 minutes')));
    }

    public function testClientLoginConsumesKeyAndRejectsReuse(): void
    {
        $user = $this->users->createUser('client@example.com', 'secret', 'client');
        $now = new DateTimeImmutable('2026-08-22 12:00:00', new DateTimeZone('UTC'));
        $generated = $this->service->generateForUser($user['id'], AccessKeyService::CLIENT_LOGIN, $now);

        self::assertNotNull($generated);
        self::assertSame($user['id'], $this->service->loginClient($generated['accessKey'], $now)['id']);
        self::assertNull($this->service->loginClient($generated['accessKey'], $now));
    }

    public function testClientLoginKeyCannotBeGeneratedForNonClient(): void
    {
        $user = $this->users->createUser('user@example.com', 'secret', 'user');

        self::assertNull($this->service->generateForUser($user['id'], AccessKeyService::CLIENT_LOGIN));
    }
}
