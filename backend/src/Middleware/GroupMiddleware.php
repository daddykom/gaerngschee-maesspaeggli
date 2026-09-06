<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Users\Data\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class GroupMiddleware
{
    public function __construct(
        private readonly array $allowedGroups,
        private readonly ?UserRepository $userRepository = null,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $userId = $request->getAttribute('user_id');
        if (!is_string($userId) || $userId === '') {
            return $this->notFound();
        }

        $user = ($this->userRepository ?? new UserRepository())->findById($userId);
        if ($user === null || !in_array($user['group'] ?? null, $this->allowedGroups, true)) {
            return $this->notFound();
        }

        return $handler->handle($request->withAttribute('user', $user));
    }

    private function notFound(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error' => [
                'code' => 'NOT_FOUND',
                'details' => [],
            ],
        ], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }
}
