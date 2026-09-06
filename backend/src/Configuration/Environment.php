<?php

declare(strict_types=1);

namespace App\Configuration;

use Dotenv\Dotenv;

final class Environment
{
    private static bool $loaded = false;

    public static function load(?string $path = null): void
    {
        if (self::$loaded) {
            return;
        }

        $directory = dirname(__DIR__, 2);
        $filename = '.env';
        $configuredFile = getenv('GAERNGSCHEE_ENV_FILE');
        if (is_string($configuredFile) && trim($configuredFile) !== '') {
            $directory = dirname($configuredFile);
            $filename = basename($configuredFile);
        } elseif ($path !== null) {
            $directory = dirname($path);
            $filename = basename($path);
        }

        Dotenv::createUnsafeImmutable($directory, $filename)->safeLoad();
        self::$loaded = true;
    }
}
