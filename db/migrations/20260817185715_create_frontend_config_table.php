<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateFrontendConfigTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('frontend_config', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'string', ['limit' => 36])
              ->addColumn('variable_name', 'string', ['limit' => 255])
              ->addColumn('access_group', 'json')
              ->addColumn('update_group', 'json')
              ->addColumn('label', 'string', ['limit' => 255])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
              ->addIndex('variable_name', ['unique' => true])
              ->create();
    }
}
