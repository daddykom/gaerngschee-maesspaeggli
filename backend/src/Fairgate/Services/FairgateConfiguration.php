<?php

declare(strict_types=1);

namespace App\Fairgate\Services;

final class FairgateConfiguration
{
    /** @return array{base_url: string, organization_id: string, access_key: string, public_key: string} */
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

        foreach (['base_url', 'organization_id', 'access_key', 'public_key'] as $key) {
            if (!isset($configuration[$key]) || !is_string($configuration[$key]) || trim($configuration[$key]) === '') {
                throw new FairgateException('Incomplete local Fairgate configuration.');
            }
        }

        return [
            'base_url' => $configuration['base_url'],
            'organization_id' => $configuration['organization_id'],
            'access_key' => $configuration['access_key'],
            'public_key' => $configuration['public_key'],
        ];
    }
}
