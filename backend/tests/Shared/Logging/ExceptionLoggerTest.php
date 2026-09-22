<?php

declare(strict_types=1);

namespace Tests\Shared\Logging;

use App\Shared\Logging\ExceptionLogger;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use InvalidArgumentException;

final class ExceptionLoggerTest extends TestCase
{
    public function testLogsExceptionClassAndMessage(): void
    {
        $logFile = tempnam(sys_get_temp_dir(), 'gaerngschee-log-');
        self::assertNotFalse($logFile);
        $previousErrorLog = ini_get('error_log');

        try {
            ini_set('error_log', $logFile);
            ExceptionLogger::log(
                'Mail delivery failed',
                new RuntimeException('SMTP failed', 0, new InvalidArgumentException('Authentication failed')),
            );

            $log = file_get_contents($logFile);
            self::assertIsString($log);
            self::assertStringContainsString('Mail delivery failed', $log);
            self::assertStringContainsString('RuntimeException', $log);
            self::assertStringContainsString('SMTP failed', $log);
            self::assertStringContainsString('InvalidArgumentException', $log);
            self::assertStringContainsString('Authentication failed', $log);
        } finally {
            ini_set('error_log', is_string($previousErrorLog) ? $previousErrorLog : '');
            unlink($logFile);
        }
    }
}
