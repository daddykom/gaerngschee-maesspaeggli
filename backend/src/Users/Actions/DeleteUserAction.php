<?php

declare(strict_types=1);

namespace App\Users\Actions;

use App\Users\Data\UserRepository;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DeleteUserAction
{
    public function __construct(private readonly ?UserRepository $users = null)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (string) ($args['userId'] ?? '');
        $deleted = ($this->users ?? new UserRepository())->deleteUser($userId);

        return $deleted
            ? JsonResponse::success($response, ['deleted' => true, 'userId' => $userId])
            : JsonResponse::error($response, 'NOT_FOUND', 404);
    }
}
