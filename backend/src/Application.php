<?php

declare(strict_types=1);

namespace App;

use App\Routes\AdminRoutes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;

final class Application
{
    public static function create(): App
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $app = AppFactory::create();

        $app->addRoutingMiddleware();

        $app->add(function (ServerRequestInterface $request, $handler): ResponseInterface {
            if ($request->getMethod() === 'OPTIONS') {
                $response = new Response();
                return $response
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
                    ->withStatus(204);
            }

            $response = $handler->handle($request);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Access-Control-Allow-Origin', '*');
        });

        $app->get('/', function ($request, ResponseInterface $response) {
            $response->getBody()->write(json_encode(['message' => 'API']));
            return $response->withHeader('Content-Type', 'application/json');
        });

        AdminRoutes::register($app);

        return $app;
    }
}
