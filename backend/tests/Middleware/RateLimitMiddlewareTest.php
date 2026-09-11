<?php

declare(strict_types=1);

namespace Tests\Middleware;

use App\Middleware\RateLimitMiddleware;
use App\Shared\Http\RateLimitService;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\Support\TestDatabase;

final class RateLimitMiddlewareTest extends TestCase
{
    private PDO $database;

    protected function setUp(): void
    {
        $this->database = TestDatabase::create();
    }

    public function testAllowsRequestsUntilLimitAndReturnsRetryAfterWhenExceeded(): void
    {
        $service = new RateLimitService($this->database);
        $now = new DateTimeImmutable('2026-09-11 12:00:00');

        self::assertNull($service->consume('ip:127.0.0.1', 2, 900, $now));
        self::assertNull($service->consume('ip:127.0.0.1', 2, 900, $now));
        self::assertSame(900, $service->consume('ip:127.0.0.1', 2, 900, $now));
    }

    public function testMiddlewareReturnsStandardRateLimitError(): void
    {
        $service = new RateLimitService($this->database);
        $middleware = new RateLimitMiddleware(1, 900, static fn ($request): string => 'test-key', $service);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/auth/login');

        $middleware->process($request, $this->createMock(RequestHandlerInterface::class));
        $response = $middleware->process($request, $handler);

        self::assertSame(429, $response->getStatusCode());
        self::assertGreaterThan(0, (int) $response->getHeaderLine('Retry-After'));
        self::assertLessThanOrEqual(900, (int) $response->getHeaderLine('Retry-After'));
        self::assertSame(['error' => ['code' => 'RATE_LIMITED', 'details' => []]], json_decode((string) $response->getBody(), true));
    }

    public function testDifferentKeysHaveIndependentCounters(): void
    {
        $service = new RateLimitService($this->database);
        $now = new DateTimeImmutable('2026-09-11 12:00:00');

        self::assertNull($service->consume('ip:one', 1, 900, $now));
        self::assertNull($service->consume('ip:two', 1, 900, $now));
    }
}
