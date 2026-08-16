<?php

declare(strict_types=1);

namespace App\Routes;

use App\Data\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Response;

final class AdminRoutes
{
    public static function register(App $app): void
    {
        $userGroup = $_SESSION['user_group'] ?? null;

        if ($userGroup === 'admin') {
            $app->get('/api/admin/users', function ($request, ResponseInterface $response) {
                $repository = new UserRepository();
                $users = $repository->findAll();
                $body = json_encode($users, JSON_THROW_ON_ERROR);
                $response->getBody()->write($body);
                return $response
                    ->withHeader('Content-Type', 'application/json');
            });
        }
    }
}
