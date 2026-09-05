<?php

declare(strict_types=1);

namespace App\Fairgate\Services;

final class FairgateConfiguration
{
    /** @return array{mode: string, base_url: string, organization_id: string, access_key: string, public_key: string} */
    public static function load(): array
    {
        $configuration = self::read();

        if (!isset($configuration['mode']) || !in_array($configuration['mode'], ['fake', 'real'], true)) {
            throw new FairgateException('Invalid local Fairgate mode.');
        }

        self::validateCredentials($configuration, $configuration['mode'] === 'real');

        return self::normalize($configuration);
    }

    /** @return array{mode: string, base_url: string, organization_id: string, access_key: string, public_key: string} */
    public static function loadReal(): array
    {
        $configuration = self::read();
        self::validateCredentials($configuration, true);

        return self::normalize([...$configuration, 'mode' => 'real']);
    }

    /** @return array<string, mixed> */
    private static function read(): array
    {
        $path = dirname(__DIR__, 3) . '/config/fairgate.local.php';
        if (!is_file($path)) {
            throw new FairgateException('Missing local Fairgate configuration.');
        }

        $configuration = require $path;
        if (!is_array($configuration)) {
            throw new FairgateException('Invalid local Fairgate configuration.');
        }

        return $configuration;
    }

    /** @param array<string, mixed> $configuration */
    private static function validateCredentials(array $configuration, bool $required): void
    {
        foreach (['base_url', 'organization_id', 'access_key', 'public_key'] as $key) {
            if (!isset($configuration[$key]) || !is_string($configuration[$key]) || trim($configuration[$key]) === '') {
                if (!$required) {
                    continue;
                }
                throw new FairgateException('Incomplete local Fairgate configuration.');
            }
        }
    }

    /** @param array<string, mixed> $configuration */
    private static function normalize(array $configuration): array
    {
        return [
            'mode' => (string) ($configuration['mode'] ?? 'real'),
            'base_url' => (string) ($configuration['base_url'] ?? ''),
            'organization_id' => (string) ($configuration['organization_id'] ?? ''),
            'access_key' => (string) ($configuration['access_key'] ?? ''),
            'public_key' => (string) ($configuration['public_key'] ?? ''),
        ];
    }
}
