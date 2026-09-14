<?php

declare(strict_types=1);

namespace Tests\Registration;

use App\Registration\Data\RegistrationTokenRepository;
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

    public function testOnlyTheTokenHashIsPersisted(): void
    {
        $pdo = TestDatabase::create();
        $service = new RegistrationTokenService($pdo);
        $issued = $service->issue('person@example.com');

        $storedHash = (string) $pdo->query('SELECT token_hash FROM registration_tokens')->fetchColumn();

        self::assertNotSame($issued['token'], $storedHash);
        self::assertSame(hash('sha256', $issued['token']), $storedHash);
    }

    public function testTokenExpiresAfterTenMinutes(): void
    {
        $now = new DateTimeImmutable('2026-08-25 12:00:00', new DateTimeZone('UTC'));
        $service = new RegistrationTokenService(TestDatabase::create());
        $issued = $service->issue('person@example.com', $now);

        self::assertNull($service->consume($issued['token'], $now->modify('+10 minutes')));
    }

    public function testIssuingANewTokenInvalidatesThePreviousTokenForTheSameEmail(): void
    {
        $now = new DateTimeImmutable('2026-08-25 12:00:00', new DateTimeZone('UTC'));
        $service = new RegistrationTokenService(TestDatabase::create());
        $first = $service->issue('person@example.com', $now);
        $second = $service->issue('person@example.com', $now->modify('+1 minute'));

        self::assertNull($service->consume($first['token'], $now->modify('+2 minutes')));
        self::assertSame('person@example.com', $service->consume($second['token'], $now->modify('+2 minutes')));
    }

    public function testMalformedTokenCannotBeConsumed(): void
    {
        $service = new RegistrationTokenService(TestDatabase::create());

        self::assertNull($service->consume('not-a-valid-registration-token'));
    }

    public function testDeletesTokensOlderThanConfiguredRetentionPeriod(): void
    {
        $pdo = TestDatabase::create();
        $service = new RegistrationTokenService($pdo);
        $old = $service->issue('old@example.com', new DateTimeImmutable('2025-08-25 11:00:00', new DateTimeZone('UTC')));
        $recent = $service->issue('recent@example.com', new DateTimeImmutable('2026-08-25 11:55:00', new DateTimeZone('UTC')));
        $tokens = new RegistrationTokenRepository($pdo);

        $deleted = $tokens->deleteExpiredBefore(new DateTimeImmutable('2026-08-25 12:00:00', new DateTimeZone('UTC')));

        self::assertSame(1, $deleted);
        self::assertNull($service->consume($old['token'], new DateTimeImmutable('2026-08-25 12:00:00', new DateTimeZone('UTC'))));
        self::assertSame('recent@example.com', $service->consume($recent['token'], new DateTimeImmutable('2026-08-25 12:00:00', new DateTimeZone('UTC'))));
    }
}
