<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDeliveryTokenToOrders extends AbstractMigration
{
    public function change(): void
    {
        $this->table('orders')
            ->addColumn('delivery_token', 'string', ['limit' => 64, 'null' => true])
            ->addIndex('delivery_token', ['unique' => true])
            ->update();
    }
}
