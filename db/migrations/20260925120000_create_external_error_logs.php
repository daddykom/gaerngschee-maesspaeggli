<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateExternalErrorLogs extends AbstractMigration
{
    public function change(): void
    {
        $this->table('external_error_logs', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('source', 'string', ['limit' => 50])
            ->addColumn('operation', 'string', ['limit' => 100])
            ->addColumn('message', 'text')
            ->addColumn('details', 'text', ['null' => true, 'default' => null])
            ->addColumn('http_status', 'integer', ['null' => true, 'default' => null])
            ->addColumn('order_id', 'string', ['limit' => 36, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex('created_at')
            ->addIndex(['source', 'created_at'])
            ->create();
    }
}
