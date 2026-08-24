<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StoreFrontendConfigValuesAsJson extends AbstractMigration
{
    public function change(): void
    {
        $adapter = $this->getAdapter();
        $update = $adapter->getConnection()->prepare(
            'UPDATE frontend_config SET value = :value WHERE id = :id',
        );

        foreach ($adapter->fetchAll('SELECT id, value FROM frontend_config WHERE value IS NOT NULL') as $row) {
            $decoded = json_decode((string) $row['value'], true);
            $value = is_string($decoded) || $this->isStringArray($decoded)
                ? $decoded
                : (string) $row['value'];

            $update->execute([
                'value' => json_encode($value, JSON_THROW_ON_ERROR),
                'id' => $row['id'],
            ]);
        }

        $this->table('frontend_config_values')->drop()->save();
    }

    private function isStringArray(mixed $value): bool
    {
        return is_array($value)
            && array_is_list($value)
            && count(array_filter($value, 'is_string')) === count($value);
    }
}
