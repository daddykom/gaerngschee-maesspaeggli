<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddValuesToFrontendConfig extends AbstractMigration
{
    public function change(): void
    {
        $this->table('frontend_config')
            ->addColumn('value', 'text')
            ->addColumn('description', 'text')
            ->update();

        $table = $this->table('frontend_config_values', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('frontend_config_id', 'string', ['limit' => 36])
            ->addColumn('value', 'text')
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
            ->addIndex('frontend_config_id')
            ->addForeignKey('frontend_config_id', 'frontend_config', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
