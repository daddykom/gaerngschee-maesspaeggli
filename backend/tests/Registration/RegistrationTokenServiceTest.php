<?php

declare(strict_types=1);

namespace Tests\Registration;

use App\Registration\Services\RegistrationTokenService;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

final class RegistrationTokenServiceTest extends TestCase
{
    public function testTokenCanBeConsumedOnceBeforeExpiry(): void
    {
        $now = new DateTimeImmutable('2026-08-25 12:00:00', new DateTimeZone('UTC'));
        $service = new RegistrationTokenService(TestDatabase::create());
        $issued = $service->issue('person@example.com', $now);

        self::assertSame('person@example.com', $service->consume($issued['token'], $now->modify('+9 minutes')));
        self::assertNull($service->consume($issued['token'], $now->modify('+9 minutes')));
    }

    public function testTokenExpiresAfterTenMinutes(): void
    {
        $now = new DateTimeImmutable('2026-08-25 12:00:00', new DateTimeZone('UTC'));
        $service = new RegistrationTokenService(TestDatabase::create());
        $issued = $service->issue('person@example.com', $now);

        self::assertNull($service->consume($issued['token'], $now->modify('+10 minutes')));
    }
}
