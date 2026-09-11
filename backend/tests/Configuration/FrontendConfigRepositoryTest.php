<?php

declare(strict_types=1);

namespace Tests\Configuration;

use App\Configuration\Data\FrontendConfigRepository;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

final class FrontendConfigRepositoryTest extends TestCase
{
    public function testMapsPatternAndPlaceholderAndValidatesScalarValue(): void
    {
        $pdo = TestDatabase::create();
        $this->insertConfig($pdo, 'config-1', '^(?:[0-9]+)$', 'z. B. 123');
        $repository = new FrontendConfigRepository($pdo);

        $config = $repository->findVisibleForGroup('admin')[0];

        self::assertSame('^(?:[0-9]+)$', $config['pattern']);
        self::assertSame('z. B. 123', $config['placeholder']);
        self::assertNotFalse($repository->update('config-1', 'admin', '123'));
        self::expectException(\InvalidArgumentException::class);
        $repository->update('config-1', 'admin', 'abc');
    }

    public function testValidatesEachArrayValueAndRejectsInvalidPatterns(): void
    {
        $pdo = TestDatabase::create();
        $this->insertConfig($pdo, 'config-2', '[A-Z]+', null);
        $repository = new FrontendConfigRepository($pdo);

        self::assertNotFalse($repository->update('config-2', 'admin', ['ABC', 'XYZ']));
        self::expectException(\InvalidArgumentException::class);
        $repository->update('config-2', 'admin', ['ABC', 'invalid']);
    }

    public function testRejectsInvalidStoredPattern(): void
    {
        $pdo = TestDatabase::create();
        $this->insertConfig($pdo, 'config-3', '[', null);

        self::expectException(\InvalidArgumentException::class);
        (new FrontendConfigRepository($pdo))->update('config-3', 'admin', 'value');
    }

    private function insertConfig(\PDO $pdo, string $id, ?string $pattern, ?string $placeholder): void
    {
        $pdo->prepare(
            'INSERT INTO frontend_config
                (id, variable_name, value, description, access_group, update_group, label, pattern, placeholder)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        )->execute([
            $id,
            $id,
            json_encode('value', JSON_THROW_ON_ERROR),
            'Description',
            '["admin"]',
            '["admin"]',
            'Label',
            $pattern,
            $placeholder,
        ]);
    }
}
