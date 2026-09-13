<?php

declare(strict_types=1);

namespace Tests\Auth;

use App\Auth\Services\PasswordResetTokenService;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

final class PasswordResetTokenServiceTest extends TestCase
{
    public function testTokenCanBeConsumedOnlyOnceBeforeExpiry(): void
    {
        $now = new DateTimeImmutable('2026-08-25 12:00:00', new DateTimeZone('UTC'));
        $service = new PasswordResetTokenService(TestDatabase::create());
        $issued = $service->issue('user-1', $now);

        self::assertSame('user-1', $service->consume($issued['token'], $now->modify('+9 minutes')));
        self::assertNull($service->consume($issued['token'], $now->modify('+9 minutes')));
        self::assertSame('2026-08-25T12:10:00+00:00', $issued['expiresAt']);
    }

    public function testTokenExpiresAfterTenMinutes(): void
    {
        $now = new DateTimeImmutable('2026-08-25 12:00:00', new DateTimeZone('UTC'));
        $service = new PasswordResetTokenService(TestDatabase::create());
        $issued = $service->issue('user-1', $now);

        self::assertNull($service->consume($issued['token'], $now->modify('+10 minutes')));
    }

    public function testIssuingANewTokenInvalidatesThePreviousToken(): void
    {
        $now = new DateTimeImmutable('2026-08-25 12:00:00', new DateTimeZone('UTC'));
        $service = new PasswordResetTokenService(TestDatabase::create());
        $first = $service->issue('user-1', $now);
        $second = $service->issue('user-1', $now->modify('+1 minute'));

        self::assertNull($service->consume($first['token'], $now->modify('+2 minutes')));
        self::assertSame('user-1', $service->consume($second['token'], $now->modify('+2 minutes')));
    }
}
