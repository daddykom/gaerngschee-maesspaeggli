<?php

declare(strict_types=1);

namespace Tests\Data;

use App\Data\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class UserRepositoryTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $repository;

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
                created_at TEXT,
                updated_at TEXT
            )',
        );
        $this->repository = new UserRepository($this->pdo);
    }

    public function testCreateUserStoresHashAndReturnsSafeUser(): void
    {
        $user = $this->repository->createUser('client@example.com', 'secret', 'client');

        self::assertSame('client@example.com', $user['email']);
        self::assertSame('client', $user['group']);
        self::assertArrayNotHasKey('password', $user);

        $stored = $this->pdo->query('SELECT password FROM users')->fetchColumn();
        self::assertIsString($stored);
        self::assertTrue(password_verify('secret', $stored));
    }

    public function testVerifyPasswordReturnsSafeUserOnlyForCorrectPassword(): void
    {
        $this->repository->createUser('user@example.com', 'secret');

        self::assertNotNull($this->repository->verifyPassword('user@example.com', 'secret'));
        self::assertNull($this->repository->verifyPassword('user@example.com', 'wrong'));
    }

    public function testFindByIdAndEmailDoNotExposePassword(): void
    {
        $created = $this->repository->createUser('admin@example.com', 'secret', 'admin');

        self::assertSame($created, $this->repository->findById($created['id']));
        self::assertSame($created, $this->repository->findByEmail('admin@example.com'));
    }
}
