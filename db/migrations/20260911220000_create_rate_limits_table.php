<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRateLimitsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('rate_limits', ['id' => false, 'primary_key' => ['key_hash', 'bucket_start']]);
        $table
            ->addColumn('key_hash', 'string', ['limit' => 64])
            ->addColumn('bucket_start', 'datetime')
            ->addColumn('request_count', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('expires_at', 'datetime')
            ->addIndex(['expires_at'])
            ->create();
    }
}
