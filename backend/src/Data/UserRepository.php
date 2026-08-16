<?php

declare(strict_types=1);

namespace App\Data;

use PDO;

final class UserRepository
{
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
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
