<?php

declare(strict_types=1);

namespace App\Fairgate\Services;

final class FairgateContactProviderFactory
{
    /** @param array{mode: string, base_url?: string, organization_id?: string, access_key?: string, public_key?: string}|null $configuration */
    public static function create(?array $configuration = null): FairgateContactProvider
    {
        $configuration ??= FairgateConfiguration::load();

        if (!in_array($configuration['mode'] ?? null, ['fake', 'real'], true)) {
            throw new \LogicException('Unknown Fairgate mode.');
        }

        return $configuration['mode'] === 'fake'
            ? new FakeFairgateClient()
            : new FairgateClient(
                baseUrl: $configuration['base_url'],
                organizationId: $configuration['organization_id'],
                accessKey: $configuration['access_key'],
                publicKey: $configuration['public_key'],
            );
    }

    /** @param array{base_url?: string, organization_id?: string, access_key?: string, public_key?: string}|null $configuration */
    public static function createReal(?array $configuration = null): FairgateContactProvider
    {
        $configuration = $configuration === null
            ? FairgateConfiguration::loadReal()
            : [...$configuration, 'mode' => 'real'];

        return self::create($configuration);
    }
}
