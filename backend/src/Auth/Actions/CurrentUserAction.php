<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Users\Data\UserRepository;
use App\Auth\Services\JwtService;
use App\Auth\Services\SessionService;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CurrentUserAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $session = new SessionService();
        $userId = $session->getUserId();
        if ($userId === null) {
            $jwt = new JwtService();
            $token = $jwt->getBearerToken($request);
            $userId = $token === null ? null : $jwt->getUserIdFromToken($token);
        }

        $user = $userId === null ? null : (new UserRepository())->findById($userId);

        return $user === null
            ? JsonResponse::error($response, 'UNAUTHORIZED', 401)
            : JsonResponse::success($response, ['user' => $user]);
    }
}
