<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddProvisionalOrderRecentDaysConfiguration extends AbstractMigration
{
    private const VARIABLE_NAME = 'provisional_order_recent_days';

    public function up(): void
    {
        if ($this->fetchRow("SELECT id FROM frontend_config WHERE variable_name = '" . self::VARIABLE_NAME . "'") !== false) {
            return;
        }

        $this->execute(
            'INSERT INTO frontend_config
                (id, variable_name, value, description, access_group, update_group, label)
             VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label)',
            [
                'id' => '00000000-0000-4000-8000-000000000014',
                'variable_name' => self::VARIABLE_NAME,
                'value' => json_encode('14', JSON_THROW_ON_ERROR),
                'description' => 'Zeitraum für aktuelle provisorische Bestellungen in Tagen.',
                'access_group' => json_encode(['admin', 'user'], JSON_THROW_ON_ERROR),
                'update_group' => json_encode(['admin'], JSON_THROW_ON_ERROR),
                'label' => 'Zeitraum provisorischer Bestellungen',
            ],
        );
    }

    public function down(): void
    {
        $this->execute(
            'DELETE FROM frontend_config WHERE variable_name = :variable_name',
            ['variable_name' => self::VARIABLE_NAME],
        );
    }
}
