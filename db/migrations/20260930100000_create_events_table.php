<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEventsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('events', ['id' => false, 'primary_key' => 'key'])
            ->addColumn('key', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('value', 'integer', ['default' => 0, 'null' => false])
            ->create();
    }
}
