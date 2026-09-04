<?php

declare(strict_types=1);

namespace App\Registration\Data;

use PDO;

final class OrderRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed>|null */
    public function findForYear(string $userId, int $year): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, user_id, year, status, adults_count, children_count,
                    confirmation_email_sent_at, fairgate_reminder_email_sent_at, created_at, updated_at
             FROM orders
             WHERE user_id = :user_id AND year = :year',
        );
        $statement->execute(['user_id' => $userId, 'year' => $year]);
        $order = $statement->fetch();
        if ($order === false) {
            return null;
        }

        $items = $this->pdo->prepare(
            'SELECT person_type, category, quantity
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY person_type, category',
        );
        $items->execute(['order_id' => $order['id']]);

        return [
            'id' => $order['id'],
            'userId' => $order['user_id'],
            'year' => (int) $order['year'],
            'status' => $order['status'],
            'adultsCount' => (int) $order['adults_count'],
            'childrenCount' => (int) $order['children_count'],
            'confirmationEmailSentAt' => $order['confirmation_email_sent_at'],
            'fairgateReminderEmailSentAt' => $order['fairgate_reminder_email_sent_at'],
            'items' => array_map(
                static fn (array $item): array => [
                    'personType' => $item['person_type'],
                    'category' => $item['category'],
                    'quantity' => (int) $item['quantity'],
                ],
                $items->fetchAll(),
            ),
            'createdAt' => $order['created_at'],
            'updatedAt' => $order['updated_at'],
        ];
    }

    /** @return list<array{order: array<string, mixed>, email: string}> */
    public function findProvisionalWithUsers(): array
    {
        $statement = $this->pdo->query(
            "SELECT orders.id, orders.user_id, users.email
             FROM orders
             INNER JOIN users ON users.id = orders.user_id
             WHERE orders.status = 'provisional'
             ORDER BY orders.created_at, orders.id",
        );

        $orders = [];
        foreach ($statement->fetchAll() as $row) {
            $order = $this->findForId((string) $row['id']);
            if ($order !== null) {
                $orders[] = ['order' => $order, 'email' => (string) $row['email']];
            }
        }

        return $orders;
    }

    /** @param list<array{personType: string, category: string, quantity: int}> $items */
    public function updateFromBatch(
        string $orderId,
        int $adultsCount,
        int $childrenCount,
        array $items,
    ): void {
        $this->pdo->beginTransaction();
        try {
            $update = $this->pdo->prepare(
                'UPDATE orders SET adults_count = :adults_count, children_count = :children_count,
                    updated_at = CURRENT_TIMESTAMP WHERE id = :id AND status = \'provisional\'',
            );
            $update->execute([
                'id' => $orderId,
                'adults_count' => $adultsCount,
                'children_count' => $childrenCount,
            ]);
            $this->pdo->prepare('DELETE FROM order_items WHERE order_id = :order_id')
                ->execute(['order_id' => $orderId]);
            $insert = $this->pdo->prepare(
                'INSERT INTO order_items (id, order_id, person_type, category, quantity)
                 VALUES (:id, :order_id, :person_type, :category, :quantity)',
            );
            foreach ($items as $item) {
                $insert->execute([
                    'id' => $this->createUuid(),
                    'order_id' => $orderId,
                    'person_type' => $item['personType'],
                    'category' => $item['category'],
                    'quantity' => $item['quantity'],
                ]);
            }
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function markBatchEmailSent(string $orderId): void
    {
        $statement = $this->pdo->prepare(
            "UPDATE orders SET status = 'definitive', confirmation_email_sent_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP WHERE id = :id",
        );
        $statement->execute(['id' => $orderId]);
    }

    public function markFairgateReminderSent(string $orderId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE orders SET fairgate_reminder_email_sent_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP WHERE id = :id',
        );
        $statement->execute(['id' => $orderId]);
    }

    /** @param list<array{personType: string, category: string, quantity: int}> $items */
    public function saveForYear(
        string $userId,
        int $year,
        string $status,
        int $adultsCount,
        int $childrenCount,
        array $items,
    ): array {
        $this->pdo->beginTransaction();

        try {
            $find = $this->pdo->prepare(
                'SELECT id FROM orders WHERE user_id = :user_id AND year = :year',
            );
            $find->execute(['user_id' => $userId, 'year' => $year]);
            $existing = $find->fetch();
            $orderId = is_array($existing) ? (string) $existing['id'] : $this->createUuid();

            if (is_array($existing)) {
                $update = $this->pdo->prepare(
                    'UPDATE orders
                     SET status = :status, adults_count = :adults_count,
                          children_count = :children_count, updated_at = CURRENT_TIMESTAMP
                          , confirmation_email_sent_at = NULL
                     WHERE id = :id',
                );
                $update->execute([
                    'id' => $orderId,
                    'status' => $status,
                    'adults_count' => $adultsCount,
                    'children_count' => $childrenCount,
                ]);
                $delete = $this->pdo->prepare('DELETE FROM order_items WHERE order_id = :order_id');
                $delete->execute(['order_id' => $orderId]);
            } else {
                $insert = $this->pdo->prepare(
                    'INSERT INTO orders
                        (id, user_id, year, status, adults_count, children_count)
                     VALUES (:id, :user_id, :year, :status, :adults_count, :children_count)',
                );
                $insert->execute([
                    'id' => $orderId,
                    'user_id' => $userId,
                    'year' => $year,
                    'status' => $status,
                    'adults_count' => $adultsCount,
                    'children_count' => $childrenCount,
                ]);
            }

            $insertItem = $this->pdo->prepare(
                'INSERT INTO order_items (id, order_id, person_type, category, quantity)
                 VALUES (:id, :order_id, :person_type, :category, :quantity)',
            );
            foreach ($items as $item) {
                $insertItem->execute([
                    'id' => $this->createUuid(),
                    'order_id' => $orderId,
                    'person_type' => $item['personType'],
                    'category' => $item['category'],
                    'quantity' => $item['quantity'],
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->findForYear($userId, $year) ?? throw new \RuntimeException('Order was not saved.');
    }

    public function markConfirmationEmailSent(string $orderId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE orders SET confirmation_email_sent_at = CURRENT_TIMESTAMP WHERE id = :id',
        );
        $statement->execute(['id' => $orderId]);
    }

    private function createUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /** @return array<string, mixed>|null */
    private function findForId(string $orderId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT user_id, year FROM orders WHERE id = :id',
        );
        $statement->execute(['id' => $orderId]);
        $row = $statement->fetch();
        return is_array($row) ? $this->findForYear((string) $row['user_id'], (int) $row['year']) : null;
    }
}
