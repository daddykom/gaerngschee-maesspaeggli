<?php

declare(strict_types=1);

namespace App\Shared\Events;

use PDO;

final class EventRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, int> */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT `key`, value FROM events ORDER BY `key`');
        $events = [];
        foreach ($statement->fetchAll() as $event) {
            $events[(string) $event['key']] = (int) $event['value'];
        }

        return $events;
    }

    public function increment(string $key, int $amount = 1): int
    {
        if ($key === '' || $amount < 1) {
            throw new \InvalidArgumentException('Event key and increment amount must be valid.');
        }

        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $statement = $this->pdo->prepare(
                'INSERT INTO events ("key", value) VALUES (:key, :value)
                 ON CONFLICT("key") DO UPDATE SET value = events.value + excluded.value',
            );
        } else {
            $statement = $this->pdo->prepare(
                'INSERT INTO events (`key`, value) VALUES (:key, :value)
                 ON DUPLICATE KEY UPDATE value = value + VALUES(value)',
            );
        }
        $statement->execute(['key' => $key, 'value' => $amount]);

        $value = $this->pdo->prepare('SELECT value FROM events WHERE `key` = :key');
        $value->execute(['key' => $key]);

        return (int) $value->fetchColumn();
    }
}
