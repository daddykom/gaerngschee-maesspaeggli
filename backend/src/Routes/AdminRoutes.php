<?php

declare(strict_types=1);

namespace App\Routes;

use App\Data\UserRepository;
use App\Middleware\AuthMiddleware;
use App\Middleware\GroupMiddleware;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class AdminRoutes
{
    public static function register(App $app): void
    {
        $app->group('/admin', function (RouteCollectorProxy $group): void {
            $route = $group->get('/users', function ($request, ResponseInterface $response) {
                $repository = new UserRepository();
                $users = $repository->findAll();
                $body = json_encode($users, JSON_THROW_ON_ERROR);
                $response->getBody()->write($body);
                return $response
                    ->withHeader('Content-Type', 'application/json');
            });

            $route->add(new GroupMiddleware('admin'));
            $route->add(new AuthMiddleware());
        });
    }
}
