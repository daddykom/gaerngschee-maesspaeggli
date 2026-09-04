<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddConfirmationEmailSentAtToOrders extends AbstractMigration
{
    public function change(): void
    {
        $this->table('orders')
            ->addColumn('confirmation_email_sent_at', 'timestamp', [
                'null' => true,
                'default' => null,
            ])
            ->update();
    }
}
