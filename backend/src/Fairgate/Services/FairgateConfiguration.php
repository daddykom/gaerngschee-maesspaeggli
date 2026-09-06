<?php

declare(strict_types=1);

namespace App\Fairgate\Services;

final class FairgateConfiguration
{
    /** @return array{mode: string, base_url: string, organization_id: string, access_key: string, public_key: string} */
    public static function load(): array
    {
        $configuration = self::fromEnvironment();

        self::validateCredentials($configuration, $configuration['mode'] === 'real');

        return self::normalize($configuration);
    }

    /** @return array{mode: string, base_url: string, organization_id: string, access_key: string, public_key: string} */
    public static function loadReal(): array
    {
        $configuration = self::fromEnvironment();
        self::validateCredentials($configuration, true);

        return self::normalize([...$configuration, 'mode' => 'real']);
    }

    /** @return array<string, mixed> */
    private static function fromEnvironment(): array
    {
        $environment = getenv('APP_ENV');
        if (!is_string($environment) || !in_array($environment, ['test', 'prod'], true)) {
            throw new FairgateException('Invalid APP_ENV. Expected test or prod.');
        }

        return [
            'mode' => getenv('FSA_MODE') ?: ($environment === 'test' ? 'fake' : 'real'),
            'base_url' => getenv('FSA_BASE_URL') ?: '',
            'organization_id' => getenv('FSA_ORGANIZATION_ID') ?: '',
            'access_key' => getenv('FSA_ACCESS_KEY') ?: '',
            'public_key' => getenv('FSA_PUBLIC_KEY') ?: '',
        ];
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
