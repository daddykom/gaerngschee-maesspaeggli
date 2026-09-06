<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRegistrationTokensTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('registration_tokens', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('email', 'string', ['limit' => 255])
            ->addColumn('token_hash', 'string', ['limit' => 64])
            ->addColumn('expires_at', 'timestamp')
            ->addColumn('used_at', 'timestamp', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
            ->addIndex('token_hash', ['unique' => true])
            ->addIndex(['email', 'used_at'])
            ->addIndex('expires_at')
            ->create();
    }
}
