<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\JwtService;
use App\Services\SessionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class AuthMiddleware
{
    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $sessionService = new SessionService();
        $userId = $sessionService->getUserId();

        if ($userId === null) {
            $jwtService = new JwtService();
            $token = $jwtService->getBearerToken($request);
            $userId = $token === null ? null : $jwtService->getUserIdFromToken($token);
        }

        if ($userId === null) {
            return $this->jsonError('Unauthorized.', 401);
        }

        return $handler->handle($request->withAttribute('user_id', $userId));
    }

    private function jsonError(string $message, int $status): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode(['error' => $message], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
