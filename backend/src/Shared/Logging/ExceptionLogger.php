<?php

declare(strict_types=1);

namespace App\Shared\Logging;

use Throwable;

final class ExceptionLogger
{
    public static function log(string $context, Throwable $exception): void
    {
        $messages = [];
        $current = $exception;
        $depth = 0;

        while ($current !== null && $depth < 5) {
            $messages[] = sprintf('%s: %s', $current::class, $current->getMessage());
            $current = $current->getPrevious();
            $depth++;
        }

        error_log($context . ' - ' . implode(' | previous: ', $messages));
    }
}
