<?php

declare(strict_types=1);

namespace Tests\Auth;

use App\Auth\Services\JwtService;
use PHPUnit\Framework\TestCase;

final class JwtServiceTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('JWT_SECRET=01234567890123456789012345678901');
        putenv('JWT_ALGORITHM=HS256');
        putenv('JWT_TTL=3600');
    }

    protected function tearDown(): void
    {
        putenv('JWT_SECRET');
        putenv('JWT_ALGORITHM');
        putenv('JWT_TTL');
    }

    public function testTokenContainsAndVerifiesUserId(): void
    {
        $service = new JwtService();
        $token = $service->createToken('user-123');

        self::assertSame('user-123', $service->getUserIdFromToken($token));
    }

    public function testInvalidTokenIsRejected(): void
    {
        self::assertNull((new JwtService())->verifyToken('invalid-token'));
    }
}
