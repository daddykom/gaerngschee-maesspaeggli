<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Users\Data\UserRepository;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthenticatedPasswordChangeAction
{
    public function __construct(private readonly ?UserRepository $users = null)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $password = JsonRequest::string(JsonRequest::body($request), 'password');
        $userId = $request->getAttribute('user_id');
        if ($password === null || !is_string($userId) || $userId === '') {
            return JsonResponse::error($response, 'INVALID_PASSWORD', 422);
        }

        $user = ($this->users ?? new UserRepository())->updatePassword($userId, $password);

        return JsonResponse::success($response, ['user' => $user]);
    }
}
