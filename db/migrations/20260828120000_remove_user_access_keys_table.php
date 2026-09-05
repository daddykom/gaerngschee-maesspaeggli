<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveUserAccessKeysTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('user_access_keys')->drop()->save();
    }
}
