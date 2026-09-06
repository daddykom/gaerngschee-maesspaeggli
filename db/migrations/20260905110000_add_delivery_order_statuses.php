<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDeliveryOrderStatuses extends AbstractMigration
{
    public function change(): void
    {
        $this->table('orders')
            ->changeColumn('status', 'enum', ['values' => ['provisional', 'definitive', 'toDeliver', 'qrcode', 'delivered']])
            ->update();
    }
}
