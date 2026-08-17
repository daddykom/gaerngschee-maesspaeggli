<?php

declare(strict_types=1);

namespace App\Data;

use PDO;

final class UserRepository
{
    private const USER_GROUPS = ['user', 'admin', 'client'];

    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, email, `group`, created_at, updated_at FROM users ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, email, `group`, created_at, updated_at FROM users WHERE email = :email',
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(string $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, email, `group`, created_at, updated_at FROM users WHERE id = :id',
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function verifyPassword(string $email, string $password): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user === false || !password_verify($password, $user['password'])) {
            return null;
        }

        return $this->findById($user['id']);
    }

    public function createUser(string $email, string $password, string $group = 'user'): array
    {
        if (!in_array($group, self::USER_GROUPS, true)) {
            throw new \InvalidArgumentException('Invalid user group.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            throw new \RuntimeException('Could not hash password.');
        }

        $id = $this->createUuid();
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (id, email, password, `group`) VALUES (:id, :email, :password, :group)',
        );
        $stmt->execute([
            'id' => $id,
            'email' => $email,
            'password' => $passwordHash,
            'group' => $group,
        ]);

        $user = $this->findById($id);
        if ($user === null) {
            throw new \RuntimeException('Created user could not be loaded.');
        }

        return $user;
    }

    private function createUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
