<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Application;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

final class AuthRoutesTest extends TestCase
{
    public function testInvalidLoginReturnsUnauthorized(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/auth/login')
            ->withBody((new \Slim\Psr7\Stream(fopen('php://temp', 'r+'))));

        $response = Application::create()->handle($request);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame(
            '{"error":{"code":"INVALID_CREDENTIALS","details":[]}}',
            (string) $response->getBody(),
        );
    }

    public function testInvalidRegistrationReturnsValidationError(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/auth/register')
            ->withBody((new \Slim\Psr7\Stream(fopen('php://temp', 'r+'))));

        $response = Application::create()->handle($request);

        self::assertSame(422, $response->getStatusCode());
    }
}
