<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddRequiredPasswordResetToUsers extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('required_password_reset', 'boolean', ['default' => false, 'after' => 'group'])
            ->update();
    }
}
