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

    public function testCorsPreflightAllowsAuthorizationHeader(): void
    {
        $response = Application::create()->handle(
            (new ServerRequestFactory())->createServerRequest('OPTIONS', '/public'),
        );

        self::assertSame(204, $response->getStatusCode());
        self::assertStringContainsString('Authorization', $response->getHeaderLine('Access-Control-Allow-Headers'));
    }
}
