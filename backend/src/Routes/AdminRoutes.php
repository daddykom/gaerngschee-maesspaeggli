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
    public static function register(App $app, ?UserRepository $userRepository = null): void
    {
        $app->group('/admin', function (RouteCollectorProxy $group) use ($userRepository): void {
            $route = $group->get('/users', function ($request, ResponseInterface $response) use ($userRepository) {
                $repository = $userRepository ?? new UserRepository();
                $users = $repository->findAll();
                $body = json_encode($users, JSON_THROW_ON_ERROR);
                $response->getBody()->write($body);
                return $response
                    ->withHeader('Content-Type', 'application/json');
            });

            $route->add(new GroupMiddleware(['admin', 'user'], $userRepository));
            $route->add(new AuthMiddleware());
        });
    }
}
