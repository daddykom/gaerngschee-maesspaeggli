<?php

declare(strict_types=1);

namespace Tests;

use App\Application;
use App\Shared\Http\RateLimitService;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Stream;
use Tests\Support\TestDatabase;

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

    public function testRejectsOversizedJsonBodies(): void
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, str_repeat('x', 65537));
        rewind($stream);
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/public/start')
            ->withBody(new Stream($stream));

        $response = Application::create(new RateLimitService(TestDatabase::create()))->handle($request);

        self::assertSame(413, $response->getStatusCode());
        self::assertStringContainsString('PAYLOAD_TOO_LARGE', (string) $response->getBody());
    }
}
