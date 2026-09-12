<?php

declare(strict_types=1);

namespace Tests\Middleware;

use App\Auth\Services\SessionService;
use App\Middleware\CsrfMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;

final class CsrfMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        (new SessionService())->clear();
    }

    protected function tearDown(): void
    {
        (new SessionService())->clear();
    }

    public function testSafeRequestReceivesCsrfCookie(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());

        $response = (new CsrfMiddleware())->__invoke(
            (new ServerRequestFactory())->createServerRequest('GET', '/public/configuration'),
            $handler,
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('XSRF-TOKEN=', $response->getHeaderLine('Set-Cookie'));
    }

    public function testAuthenticatedMutationRequiresCsrfToken(): void
    {
        (new SessionService())->setUser('user-123', 'user');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $response = (new CsrfMiddleware())->__invoke(
            (new ServerRequestFactory())->createServerRequest('POST', '/client/order'),
            $handler,
        );

        self::assertSame(403, $response->getStatusCode());
        self::assertStringContainsString('CSRF_TOKEN_INVALID', (string) $response->getBody());
    }

    public function testAuthenticatedMutationAcceptsSessionCsrfToken(): void
    {
        $session = new SessionService();
        $session->setUser('user-123', 'user');
        $token = $session->getCsrfToken();
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())->method('handle')->willReturn(new Response());

        $response = (new CsrfMiddleware())->__invoke(
            (new ServerRequestFactory())
                ->createServerRequest('POST', '/client/order')
                ->withHeader('X-CSRF-Token', $token),
            $handler,
        );

        self::assertSame(200, $response->getStatusCode());
    }
}
