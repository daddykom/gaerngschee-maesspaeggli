<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'string', ['limit' => 36])
              ->addColumn('email', 'string', ['limit' => 255])
              ->addColumn('password', 'string', ['limit' => 255])
              ->addColumn('group', 'enum', ['values' => ['user', 'admin', 'client'], 'default' => 'user'])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
              ->addIndex('email', ['unique' => true])
              ->create();
    }
}
