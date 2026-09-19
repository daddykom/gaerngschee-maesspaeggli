<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSortOrderToFrontendConfig extends AbstractMigration
{
    public function change(): void
    {
        $this->table('frontend_config')
            ->addColumn('sort_order', 'integer', ['default' => 0, 'after' => 'variable_name'])
            ->update();
    }
}
