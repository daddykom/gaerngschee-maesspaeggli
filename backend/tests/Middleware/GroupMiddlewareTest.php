<?php

declare(strict_types=1);

namespace Tests\Middleware;

use App\Middleware\GroupMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;

final class GroupMiddlewareTest extends TestCase
{
    public function testRequestWithoutAuthenticatedUserIsNotFound(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $response = (new GroupMiddleware('admin'))->__invoke(
            (new ServerRequestFactory())->createServerRequest('GET', '/admin/users'),
            $handler,
        );

        self::assertSame(404, $response->getStatusCode());
    }
}
