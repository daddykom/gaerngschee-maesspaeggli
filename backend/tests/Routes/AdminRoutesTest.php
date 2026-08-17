<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Application;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

final class AdminRoutesTest extends TestCase
{
    public function testAdminUsersRequiresAuthentication(): void
    {
        $response = Application::create()->handle(
            (new ServerRequestFactory())->createServerRequest('GET', '/admin/users'),
        );

        self::assertSame(401, $response->getStatusCode());
    }
}
