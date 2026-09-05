<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPositionToFrontendConfigValues extends AbstractMigration
{
    public function change(): void
    {
        $this->table('frontend_config_values')
            ->addColumn('position', 'integer', ['default' => 0])
            ->addIndex(['frontend_config_id', 'position'], ['unique' => true])
            ->update();
    }
}
