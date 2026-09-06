<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Auth\Services\JwtService;
use App\Auth\Services\SessionService;
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
            return $this->jsonError('NOT_FOUND', 404);
        }

        return $handler->handle($request->withAttribute('user_id', $userId));
    }

    private function jsonError(string $code, int $status): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error' => [
                'code' => $code,
                'details' => [],
            ],
        ], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
