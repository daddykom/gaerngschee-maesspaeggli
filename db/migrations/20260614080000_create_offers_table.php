<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateOffersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('offers', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'string', ['limit' => 36])
              ->addColumn('title', 'string', ['limit' => 255])
              ->addColumn('description', 'text', ['null' => true])
              ->addColumn('category', 'string', ['limit' => 50])
              ->addColumn('latitude', 'decimal', ['precision' => 10, 'scale' => 8, 'null' => true])
              ->addColumn('longitude', 'decimal', ['precision' => 11, 'scale' => 8, 'null' => true])
              ->addColumn('address', 'string', ['limit' => 500, 'null' => true])
              ->addColumn('status', 'enum', ['values' => ['draft', 'pending', 'published', 'archived'], 'default' => 'published'])
              ->addColumn('contact_name', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('contact_email', 'string', ['limit' => 255, 'null' => true])
              ->addColumn('contact_phone', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('image_url', 'string', ['limit' => 500, 'null' => true])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
              ->create();
    }
}