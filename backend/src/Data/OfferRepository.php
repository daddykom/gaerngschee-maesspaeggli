<?php

declare(strict_types=1);

namespace App\Data;

use PDO;

final class OfferRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM offers ORDER BY created_at DESC');
        $rows = $stmt->fetchAll();
        return array_map(fn($row) => $this->transformToApiFormat($row), $rows);
    }


}