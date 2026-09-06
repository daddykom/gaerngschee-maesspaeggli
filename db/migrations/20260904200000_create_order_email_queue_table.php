<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateOrderEmailQueueTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('orders')
            ->addColumn('fairgate_reminder_email_sent_at', 'timestamp', [
                'null' => true,
                'default' => null,
            ])
            ->update();

        $this->table('order_email_queue', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('order_id', 'string', ['limit' => 36])
            ->addColumn('email_type', 'string', ['limit' => 50])
            ->addColumn('recipient', 'string', ['limit' => 255])
            ->addColumn('subject', 'text')
            ->addColumn('html_body', 'text')
            ->addColumn('text_body', 'text')
            ->addColumn('last_error', 'text', ['null' => true, 'default' => null])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['order_id', 'email_type'], ['unique' => true])
            ->addIndex('created_at')
            ->addForeignKey('order_id', 'orders', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
