<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPatternAndPlaceholderToFrontendConfig extends AbstractMigration
{
    public function change(): void
    {
        $this->table('frontend_config')
            ->addColumn('pattern', 'string', ['limit' => 1000, 'null' => true, 'default' => null])
            ->addColumn('placeholder', 'string', ['limit' => 255, 'null' => true, 'default' => null])
            ->update();
    }
}
