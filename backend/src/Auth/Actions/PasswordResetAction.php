<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Auth\Services\PasswordResetTokenService;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use App\Users\Data\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class PasswordResetAction
{
    public function __construct(
        private readonly ?PasswordResetTokenService $tokens = null,
        private readonly ?UserRepository $users = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = JsonRequest::body($request);
        $token = JsonRequest::string($data, 'token');
        $password = JsonRequest::string($data, 'password');
        if ($token === null || $password === null || $password === '') {
            return JsonResponse::error($response, 'INVALID_PASSWORD_RESET', 422);
        }

        try {
            $userId = ($this->tokens ?? new PasswordResetTokenService())->consume($token);
            if ($userId === null) {
                return JsonResponse::error($response, 'INVALID_PASSWORD_RESET', 401);
            }

            $user = ($this->users ?? new UserRepository())->updatePassword($userId, $password);
        } catch (Throwable) {
            return JsonResponse::error($response, 'PASSWORD_RESET_FAILED', 503);
        }

        return JsonResponse::success($response, ['user' => $user]);
    }
}
