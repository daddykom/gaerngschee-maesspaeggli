<?php

declare(strict_types=1);

namespace Tests;

use App\Application;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

final class ApplicationTest extends TestCase
{
    public function testApplicationBootstraps(): void
    {
        self::assertNotNull(Application::create());
    }

    public function testPublicRouteIsAvailable(): void
    {
        $response = Application::create()->handle(
            (new ServerRequestFactory())->createServerRequest('GET', '/public'),
        );

        self::assertSame(200, $response->getStatusCode());
    }
}
