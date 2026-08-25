<?php

declare(strict_types=1);

namespace Tests\Auth;

use App\Auth\Services\SessionService;
use PHPUnit\Framework\TestCase;

final class SessionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        session_id('test-session');
        session_start();
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }
    }

    public function testUserIdCanBeStoredAndRead(): void
    {
        $service = new SessionService();
        $service->setUserId('user-123');

        self::assertSame('user-123', $service->getUserId());
    }

    public function testUserIdAndGroupCanBeStoredAndRead(): void
    {
        $service = new SessionService();
        $service->setUser('user-123', 'admin');

        self::assertSame('user-123', $service->getUserId());
        self::assertSame('admin', $service->getGroup());
    }

    public function testClearRemovesUserId(): void
    {
        $service = new SessionService();
        $service->setUserId('user-123');
        $service->clear();

        self::assertNull($service->getUserId());
        self::assertNull($service->getGroup());
    }
}
