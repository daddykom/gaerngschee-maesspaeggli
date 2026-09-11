<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetTokens extends AbstractMigration
{
    public function change(): void
    {
        $this->table('password_reset_tokens', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('user_id', 'string', ['limit' => 36])
            ->addColumn('token_hash', 'string', ['limit' => 64])
            ->addColumn('expires_at', 'timestamp')
            ->addColumn('used_at', 'timestamp', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
            ->addIndex('token_hash', ['unique' => true])
            ->addIndex(['user_id', 'used_at'])
            ->addIndex('expires_at')
            ->create();
    }
}
