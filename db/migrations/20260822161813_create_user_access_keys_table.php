<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserAccessKeysTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('user_access_keys', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'string', ['limit' => 36])
              ->addColumn('user_id', 'string', ['limit' => 36])
              ->addColumn('purpose', 'string', ['limit' => 32])
              ->addColumn('key_hash', 'string', ['limit' => 64])
              ->addColumn('expires_at', 'timestamp')
              ->addColumn('used_at', 'timestamp', ['null' => true, 'default' => null])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
              ->addIndex('key_hash', ['unique' => true])
              ->addIndex(['user_id', 'purpose', 'used_at'])
              ->addIndex('expires_at')
              ->create();
    }
}
