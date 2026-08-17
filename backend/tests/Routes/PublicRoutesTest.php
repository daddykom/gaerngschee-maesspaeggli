<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Application;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

final class PublicRoutesTest extends TestCase
{
    public function testPublicRouteReturnsSuccess(): void
    {
        $response = Application::create()->handle(
            (new ServerRequestFactory())->createServerRequest('GET', '/public'),
        );

        self::assertSame(200, $response->getStatusCode());
    }
}
