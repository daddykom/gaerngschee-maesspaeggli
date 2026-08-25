<?php

declare(strict_types=1);

namespace App\Configuration\Data;

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
            $configs[] = $this->mapConfig($row, in_array($group, $updateGroups, true));
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

        $stmt = $this->pdo->prepare(
            'UPDATE frontend_config
             SET value = :value, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id',
        );
        $stmt->execute([
            'id' => $id,
            'value' => json_encode($value, JSON_THROW_ON_ERROR),
        ]);

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

    private function mapConfig(array $row, bool $canUpdate): array
    {
        return [
            'id' => $row['id'],
            'variableName' => $row['variable_name'],
            'value' => $this->decodeValue($row['value']),
            'description' => $row['description'],
            'label' => $row['label'],
            'canUpdate' => $canUpdate,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }

    private function decodeValue(mixed $value): string|array|null
    {
        if ($value === null) {
            return null;
        }

        $decoded = json_decode((string) $value, true);
        if (is_string($decoded) || $this->isStringArray($decoded)) {
            return $decoded;
        }

        return (string) $value;
    }

    private function decodeGroups(mixed $groups): array
    {
        $decoded = is_string($groups) ? json_decode($groups, true) : $groups;

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }

    private function isStringArray(mixed $value): bool
    {
        return is_array($value)
            && array_is_list($value)
            && count(array_filter($value, 'is_string')) === count($value);
    }
}
