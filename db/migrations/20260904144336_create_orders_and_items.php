<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateOrdersAndItems extends AbstractMigration
{
    public function change(): void
    {
        $this->table('orders', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('user_id', 'string', ['limit' => 36])
            ->addColumn('year', 'integer')
            ->addColumn('status', 'enum', ['values' => ['provisorisch', 'definitiv']])
            ->addColumn('adults_count', 'integer', ['default' => 0])
            ->addColumn('children_count', 'integer', ['default' => 0])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['user_id', 'year'], ['unique' => true])
            ->addIndex('user_id')
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();

        $this->table('order_items', ['id' => false, 'primary_key' => 'id'])
            ->addColumn('id', 'string', ['limit' => 36])
            ->addColumn('order_id', 'string', ['limit' => 36])
            ->addColumn('person_type', 'enum', ['values' => ['adult', 'child']])
            ->addColumn('category', 'enum', ['values' => ['catA', 'catB', 'catC', 'catD', 'catE', 'catF', 'catG']])
            ->addColumn('quantity', 'integer', ['default' => 0])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['order_id', 'person_type', 'category'], ['unique' => true])
            ->addIndex('order_id')
            ->addForeignKey('order_id', 'orders', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
