<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Users\Data\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class SuperuserMiddleware
{
    public function __construct(private readonly ?UserRepository $users = null)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $userId = $request->getAttribute('user_id');
        if (!is_string($userId) || $userId === '') {
            return $this->forbidden();
        }

        if (!($this->users ?? new UserRepository())->isSuperuser($userId)) {
            return $this->forbidden();
        }

        return $handler->handle($request);
    }

    private function forbidden(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error' => [
                'code' => 'FORBIDDEN',
                'details' => [],
            ],
        ], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(403);
    }
}
