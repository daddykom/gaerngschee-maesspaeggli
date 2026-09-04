<?php

declare(strict_types=1);

namespace App\Registration\Data;

use PDO;

final class OrderEmailQueueRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @param array{subject: string, html: string, text: string} $message */
    public function enqueue(string $orderId, string $type, string $recipient, array $message, string $error): void
    {
        $values = [
            'id' => $this->uuid(),
            'order_id' => $orderId,
            'email_type' => $type,
            'recipient' => $recipient,
            'subject' => $message['subject'],
            'html_body' => $message['html'],
            'text_body' => $message['text'],
            'last_error' => $error,
        ];
        $existing = $this->pdo->prepare(
            'SELECT id FROM order_email_queue WHERE order_id = :order_id AND email_type = :email_type',
        );
        $existing->execute(['order_id' => $orderId, 'email_type' => $type]);
        if ($existing->fetchColumn() !== false) {
            $statement = $this->pdo->prepare(
                'UPDATE order_email_queue SET last_error = :last_error, updated_at = CURRENT_TIMESTAMP
                 WHERE order_id = :order_id AND email_type = :email_type',
            );
            $statement->execute(['last_error' => $error, 'order_id' => $orderId, 'email_type' => $type]);
            return;
        }
        $statement = $this->pdo->prepare(
            'INSERT INTO order_email_queue
                (id, order_id, email_type, recipient, subject, html_body, text_body, last_error)
             VALUES (:id, :order_id, :email_type, :recipient, :subject, :html_body, :text_body, :last_error)',
        );
        $statement->execute($values);
    }

    /** @return list<array<string, mixed>> */
    public function pending(): array
    {
        return $this->pdo->query(
            'SELECT id, order_id, email_type, recipient, subject, html_body, text_body
             FROM order_email_queue ORDER BY created_at, id',
        )->fetchAll();
    }

    public function hasPendingForOrder(string $orderId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM order_email_queue WHERE order_id = :order_id LIMIT 1');
        $statement->execute(['order_id' => $orderId]);
        return $statement->fetchColumn() !== false;
    }

    public function remove(string $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM order_email_queue WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function recordFailure(string $id, string $error): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE order_email_queue SET last_error = :last_error, updated_at = CURRENT_TIMESTAMP WHERE id = :id',
        );
        $statement->execute(['id' => $id, 'last_error' => $error]);
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
