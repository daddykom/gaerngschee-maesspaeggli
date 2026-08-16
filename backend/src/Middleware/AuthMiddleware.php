<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthMiddleware
{
    public function __invoke(ServerRequestInterface $request, $handler): ResponseInterface
    {
        if (empty($_SESSION['user_id'])) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        return $handler->handle($request);
    }
}
