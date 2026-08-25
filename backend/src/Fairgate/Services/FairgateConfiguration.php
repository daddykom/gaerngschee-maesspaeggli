<?php

declare(strict_types=1);

namespace App\Fairgate\Services;

final class FairgateConfiguration
{
    /** @return array{mode: string, base_url: string, organization_id: string, access_key: string, public_key: string} */
    public static function load(): array
    {
        $path = dirname(__DIR__, 3) . '/config/fairgate.local.php';
        if (!is_file($path)) {
            throw new FairgateException('Missing local Fairgate configuration.');
        }

        $configuration = require $path;
        if (!is_array($configuration)) {
            throw new FairgateException('Invalid local Fairgate configuration.');
        }

        if (!isset($configuration['mode']) || !in_array($configuration['mode'], ['fake', 'real'], true)) {
            throw new FairgateException('Invalid local Fairgate mode.');
        }

        foreach (['base_url', 'organization_id', 'access_key', 'public_key'] as $key) {
            if (!isset($configuration[$key]) || !is_string($configuration[$key]) || trim($configuration[$key]) === '') {
                if ($configuration['mode'] === 'fake') {
                    continue;
                }
                throw new FairgateException('Incomplete local Fairgate configuration.');
            }
        }

        return [
            'mode' => $configuration['mode'],
            'base_url' => (string) ($configuration['base_url'] ?? ''),
            'organization_id' => (string) ($configuration['organization_id'] ?? ''),
            'access_key' => (string) ($configuration['access_key'] ?? ''),
            'public_key' => (string) ($configuration['public_key'] ?? ''),
        ];
    }
}
