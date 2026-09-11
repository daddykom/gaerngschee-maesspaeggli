<?php

declare(strict_types=1);

namespace App\Configuration\Data;

use PDO;
use DateTimeImmutable;
use DateTimeZone;

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

    public function findValuesForGroup(string $group): array
    {
        $stmt = $this->pdo->query(
            'SELECT variable_name, value, access_group
             FROM frontend_config
             ORDER BY variable_name',
        );

        $configs = [];
        foreach ($stmt->fetchAll() as $row) {
            if (!in_array($group, $this->decodeGroups($row['access_group']), true)) {
                continue;
            }

            $configs[] = [
                'variableName' => $row['variable_name'],
                'value' => $this->decodeValue($row['value']),
            ];
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

    public function findValueByVariableName(string $variableName): string|array|null
    {
        $stmt = $this->pdo->prepare(
            'SELECT value FROM frontend_config WHERE variable_name = :variable_name',
        );
        $stmt->execute(['variable_name' => $variableName]);
        $value = $stmt->fetchColumn();

        return $value === false ? null : $this->decodeValue($value);
    }

    public function findCampaignYear(): int
    {
        $value = $this->findValueByVariableName('campaign_year');
        if (!is_string($value) || !ctype_digit($value) || (int) $value < 1) {
            throw new \RuntimeException('Invalid campaign year configuration.');
        }

        return (int) $value;
    }

    public function campaignStatus(?DateTimeImmutable $now = null): string
    {
        $timezone = new DateTimeZone('Europe/Zurich');
        $startValue = $this->findValueByVariableName('campaign_start_date');
        $endValue = $this->findValueByVariableName('campaign_end_date');
        $startDate = is_string($startValue) ? DateTimeImmutable::createFromFormat('!Y-m-d', $startValue, $timezone) : false;
        $endDate = is_string($endValue) ? DateTimeImmutable::createFromFormat('!Y-m-d', $endValue, $timezone) : false;
        if ($startDate === false || $startDate->format('Y-m-d') !== $startValue) {
            throw new \RuntimeException('Invalid campaign start date configuration.');
        }
        if ($endDate === false || $endDate->format('Y-m-d') !== $endValue) {
            throw new \RuntimeException('Invalid campaign end date configuration.');
        }

        $currentDate = ($now ?? new DateTimeImmutable('now', $timezone))->setTimezone($timezone);

        if ($currentDate < $startDate) {
            return 'not_started';
        }
        if ($currentDate >= $endDate->modify('+1 day')) {
            return 'ended';
        }

        return 'open';
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
