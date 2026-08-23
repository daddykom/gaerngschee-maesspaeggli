<?php

declare(strict_types=1);

namespace App\Data;

use PDO;

final class FrontendConfigRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findVisibleForGroup(string $group): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, variable_name, value, description, access_group, update_group, label, created_at, updated_at
             FROM frontend_config
             ORDER BY variable_name',
        );

        $configs = [];
        foreach ($stmt->fetchAll() as $row) {
            $accessGroups = $this->decodeGroups($row['access_group']);
            if (!in_array($group, $accessGroups, true)) {
                continue;
            }

            $updateGroups = $this->decodeGroups($row['update_group']);
            $values = $this->findValues($row['id']);
            $configs[] = $this->mapConfig($row, $values, in_array($group, $updateGroups, true));
        }

        return $configs;
    }

    public function update(string $id, string $group, string|array $value): array|false|null
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, variable_name, value, description, access_group, update_group, label, created_at, updated_at
             FROM frontend_config
             WHERE id = :id',
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        $accessGroups = $this->decodeGroups($row['access_group']);
        $updateGroups = $this->decodeGroups($row['update_group']);
        if (!in_array($group, $accessGroups, true) || !in_array($group, $updateGroups, true)) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            if (is_array($value)) {
                $this->pdo->prepare(
                    'UPDATE frontend_config SET value = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = :id',
                )->execute(['id' => $id]);

                $this->pdo->prepare('DELETE FROM frontend_config_values WHERE frontend_config_id = :id')
                    ->execute(['id' => $id]);

                $insert = $this->pdo->prepare(
                    'INSERT INTO frontend_config_values (id, frontend_config_id, value, position)
                     VALUES (:id, :frontend_config_id, :value, :position)',
                );
                foreach (array_values($value) as $position => $item) {
                    $insert->execute([
                        'id' => $this->createUuid(),
                        'frontend_config_id' => $id,
                        'value' => $item,
                        'position' => $position,
                    ]);
                }
            } else {
                $this->pdo->prepare(
                    'UPDATE frontend_config SET value = :value, updated_at = CURRENT_TIMESTAMP WHERE id = :id',
                )->execute(['id' => $id, 'value' => $value]);
                $this->pdo->prepare('DELETE FROM frontend_config_values WHERE frontend_config_id = :id')
                    ->execute(['id' => $id]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->findById($id, $group);
    }

    private function findById(string $id, string $group): ?array
    {
        foreach ($this->findVisibleForGroup($group) as $config) {
            if ($config['id'] === $id) {
                return $config;
            }
        }

        return null;
    }

    private function findValues(string $configId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT value FROM frontend_config_values
             WHERE frontend_config_id = :frontend_config_id
             ORDER BY position ASC',
        );
        $stmt->execute(['frontend_config_id' => $configId]);

        return array_column($stmt->fetchAll(), 'value');
    }

    private function mapConfig(array $row, array $values, bool $canUpdate): array
    {
        return [
            'id' => $row['id'],
            'variableName' => $row['variable_name'],
            'value' => $values === [] ? $row['value'] : $values,
            'description' => $row['description'],
            'label' => $row['label'],
            'canUpdate' => $canUpdate,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }

    private function decodeGroups(mixed $groups): array
    {
        $decoded = is_string($groups) ? json_decode($groups, true) : $groups;

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }

    private function createUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
