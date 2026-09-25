<?php

declare(strict_types=1);

namespace App\Shared\Logging;

use App\Shared\Database\Database;
use Throwable;

final class ExternalErrorLogger
{
    public static function log(
        string $source,
        string $operation,
        string $message,
        ?string $details = null,
        ?int $httpStatus = null,
        ?string $orderId = null,
    ): void {
        try {
            (new ExternalErrorLogRepository(Database::getConnection()))->create(
                $source,
                $operation,
                $message,
                $details,
                $httpStatus,
                $orderId,
            );
        } catch (Throwable $exception) {
            error_log('External error log persistence failed: ' . $exception->getMessage());
        }
    }
}
