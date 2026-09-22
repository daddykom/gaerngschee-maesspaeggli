<?php

declare(strict_types=1);

namespace App\Shared\Logging;

use Throwable;

final class ExceptionLogger
{
    public static function log(string $context, Throwable $exception): void
    {
        error_log(sprintf(
            '%s (%s): %s',
            $context,
            $exception::class,
            $exception->getMessage(),
        ));
    }
}
