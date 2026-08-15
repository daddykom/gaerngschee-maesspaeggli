# Database Conventions

## Overview

Database schema management using MariaDB with Phinx migrations.

## Phinx Migration Tool

[Phinx](https://phinx.org/) is used for database migrations. It allows version-controlled schema changes that can be rolled back.

### Installation

```bash
cd backend
composer require robmorgan/phinx
composer require --dev phpunit/phpunit
```

### Project Structure

```
db/
├── phinx.php                    # Phinx configuration
├── migrations/                  # Database migrations
│   ├── 20260614080000_create_offers_table.php
│   └── 20260614081000_create_categories_table.php
└── seeds/
    ├── development/             # Development and test data
    │   ├── OfferTestSeeder.php
    │   └── CategoryTestSeeder.php
    ├── test/                    # Minimal test data for CI
    │   └── OfferTestSeeder.php
    └── production/              # Production initial data
        └── InitialCategorySeeder.php
```

## Phinx Configuration

### db/phinx.php

```php
<?php
declare(strict_types=1);

return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
        'seeds' => __DIR__ . '/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'development' => [
            'adapter' => 'mysql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'name' => getenv('DB_NAME') ?: 'gaerngschee_dev',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
            'port' => getenv('DB_PORT') ?: '3306',
            'charset' => 'utf8mb4',
        ],
        'test' => [
            'adapter' => 'mysql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'name' => getenv('DB_TEST_NAME') ?: 'gaerngschee_test',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
            'port' => getenv('DB_PORT') ?: '3306',
            'charset' => 'utf8mb4',
        ],
        'production' => [
            'adapter' => 'mysql',
            'host' => getenv('DB_HOST'),
            'name' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'pass' => getenv('DB_PASS'),
            'port' => getenv('DB_PORT') ?: '3306',
            'charset' => 'utf8mb4',
        ],
    ],
];
```

## Commands

| Command | Description |
|---------|-------------|
| `phinx create <name>` | Create new migration |
| `phinx migrate` | Run all pending migrations |
| `phinx rollback` | Rollback last migration |
| `phinx status` | Show migration status |
| `phinx seed:run -e <environment>` | Run seeds for specific environment |
| `phinx break` | Rollback all migrations |

### Environment-Specific Seeding

```bash
phinx seed:run -e development   # Seeds all data (dev + test + prod seeders)
phinx seed:run -e test          # Seeds test environment only
phinx seed:run -e production    # Seeds production environment only
```

## Migration Example

```php
<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateOffersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('offers', ['id' => false, 'primary_key' => 'id']);
        $table->addColumn('id', 'uuid')
              ->addColumn('title', 'string', ['limit' => 255])
              ->addColumn('description', 'text')
              ->addColumn('category', 'string', ['limit' => 50])
              ->addColumn('address', 'string', ['limit' => 500])
              ->addColumn('latitude', 'decimal', ['precision' => 10, 'scale' => 8])
              ->addColumn('longitude', 'decimal', ['precision' => 11, 'scale' => 8])
              ->addColumn('status', 'enum', ['values' => ['draft', 'pending', 'published', 'archived']])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'timestamp', ['update' => 'CURRENT_TIMESTAMP'])
              ->create();
    }
}
```

## Seed Subdirectories

### development/

Contains seeders with realistic test data for local development and manual testing.

```php
<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class OfferTestSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                'title' => 'Free Lunch',
                'description' => 'Free lunch for everyone',
                'category' => 'essen',
                'status' => 'published',
            ],
            [
                'id' => 'b2c3d4e5-f6a7-8901-bcde-f12345678901',
                'title' => 'Yoga Class',
                'description' => 'Free yoga every Thursday',
                'category' => 'sport',
                'status' => 'published',
            ],
        ];

        $this->table('offers')->insert($data)->save();
    }
}
```

### test/

Minimal seeders for CI/testing. Only essential data needed for tests to run.

### production/

Seeders for production initial data only (e.g., default categories).

## Docker Integration

For local development with Docker:

```yaml
# docker-compose.yml
services:
  db:
    image: mariadb:10.11
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: gaerngschee_dev
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

## Environment Variables

```bash
DB_HOST=localhost
DB_PORT=3306
DB_NAME=gaerngschee_dev
DB_TEST_NAME=gaerngschee_test
DB_USER=root
DB_PASS=root
```

## Coding Standards

### Migration Rules

1. Always use `change()` method (allows rollback)
2. Use UUID for primary keys
3. Include `created_at` and `updated_at` timestamps
4. Use meaningful migration names: `create_<table>_table`, `add_<column>_to_<table>`
5. Timestamp format: `YYYYMMDDHHMMSS` (e.g., `20260614080000`)

### Example: Adding a Column

```php
<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddContactEmailToOffersTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('offers')
             ->addColumn('contact_email', 'string', ['limit' => 255, 'null' => true])
             ->update();
    }
}
```

## See Also

- [backend-conventions.md](./backend-conventions.md) - PHP backend conventions
- [openspec/specs/database/spec.md](../openspec/specs/database/spec.md) - Database capability spec