<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSuperuserToUsers extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('superuser', 'boolean', ['default' => false, 'after' => 'group'])
            ->update();
    }
}
