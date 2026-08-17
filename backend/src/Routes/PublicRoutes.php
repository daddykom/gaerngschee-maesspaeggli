<?php

declare(strict_types=1);

namespace App\Routes;

use Psr\Http\Message\ResponseInterface;
use Slim\App;

final class PublicRoutes
{
    public static function register(App $app): void
    {
        $app->get('/public', function ($request, ResponseInterface $response) {
            $response->getBody()->write(json_encode(['message' => 'API']));

            return $response->withHeader('Content-Type', 'application/json');
        });
    }
}
