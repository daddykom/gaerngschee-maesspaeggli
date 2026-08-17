<?php

declare(strict_types=1);

namespace Tests\Middleware;

use App\Middleware\AuthMiddleware;
use App\Services\JwtService;
use App\Services\SessionService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;

final class AuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('JWT_SECRET=01234567890123456789012345678901');
        (new SessionService())->clear();
    }

    protected function tearDown(): void
    {
        putenv('JWT_SECRET');
        (new SessionService())->clear();
    }

    public function testUnauthenticatedRequestIsRejected(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $response = (new AuthMiddleware())
            ->__invoke(
                (new ServerRequestFactory())->createServerRequest('GET', '/admin/users'),
                $handler,
            );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testBearerTokenAddsUserIdToRequest(): void
    {
        $token = (new JwtService())->createToken('user-123');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())
            ->method('handle')
            ->with(self::callback(static fn ($request): bool => $request->getAttribute('user_id') === 'user-123'))
            ->willReturn(new Response());

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/admin/users')
            ->withHeader('Authorization', 'Bearer ' . $token);

        self::assertSame(200, (new AuthMiddleware())->__invoke($request, $handler)->getStatusCode());
    }

    public function testInvalidBearerTokenIsRejected(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');
        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/admin/users')
            ->withHeader('Authorization', 'Bearer invalid-token');

        $response = (new AuthMiddleware())->__invoke($request, $handler);

        self::assertSame(401, $response->getStatusCode());
    }
}
