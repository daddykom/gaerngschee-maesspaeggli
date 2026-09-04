<?php

declare(strict_types=1);

namespace App;

use App\Routes\AdminRoutes;
use App\Routes\ConfigurationRoutes;
use App\Routes\AuthRoutes;
use App\Routes\ClientRoutes;
use App\Routes\PublicRoutes;
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
        $frontendOrigin = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/');

        $app->add(function (ServerRequestInterface $request, $handler) use ($frontendOrigin): ResponseInterface {
            if ($request->getMethod() === 'OPTIONS') {
                $response = new Response();
                return $response
                    ->withHeader('Access-Control-Allow-Origin', $frontendOrigin)
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
                    ->withHeader('Access-Control-Allow-Credentials', 'true')
                    ->withStatus(204);
            }

            $response = $handler->handle($request);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Access-Control-Allow-Origin', $frontendOrigin)
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        });

        PublicRoutes::register($app);
        AuthRoutes::register($app);
        ClientRoutes::register($app);
        AdminRoutes::register($app);
        ConfigurationRoutes::register($app);

        return $app;
    }
}
