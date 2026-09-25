<?php

declare(strict_types=1);

namespace App\Shared\Logging;

use PDO;

final class ExternalErrorLogRepository
{
    private const DEFAULT_MAX_ENTRIES = 5000;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(
        string $source,
        string $operation,
        string $message,
        ?string $details = null,
        ?int $httpStatus = null,
        ?string $orderId = null,
    ): void {
        $statement = $this->pdo->prepare(
            'INSERT INTO external_error_logs
                (id, source, operation, message, details, http_status, order_id)
             VALUES (:id, :source, :operation, :message, :details, :http_status, :order_id)',
        );
        $statement->execute([
            'id' => $this->uuid(),
            'source' => $source,
            'operation' => $operation,
            'message' => $message,
            'details' => $details,
            'http_status' => $httpStatus,
            'order_id' => $orderId,
        ]);
    }

    public function pruneToConfiguredLimit(): int
    {
        $configured = getenv('EXTERNAL_ERROR_LOG_MAX_ENTRIES');
        $limit = is_string($configured) && ctype_digit($configured) && (int) $configured > 0
            ? (int) $configured
            : self::DEFAULT_MAX_ENTRIES;

        $count = (int) $this->pdo->query('SELECT COUNT(*) FROM external_error_logs')->fetchColumn();
        $excess = $count - $limit;
        if ($excess <= 0) {
            return 0;
        }

        $statement = $this->pdo->query(
            'SELECT id FROM external_error_logs ORDER BY created_at ASC, id ASC LIMIT ' . $excess,
        );
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);
        if ($ids === []) {
            return 0;
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $delete = $this->pdo->prepare('DELETE FROM external_error_logs WHERE id IN (' . $placeholders . ')');
        $delete->execute($ids);

        return $delete->rowCount();
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
