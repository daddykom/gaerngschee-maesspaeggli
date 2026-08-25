<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Users\Data\UserRepository;
use App\Auth\Services\JwtService;
use App\Auth\Services\SessionService;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LoginAction
{
    public function __construct(
        private readonly ?UserRepository $users = null,
        private readonly ?JwtService $jwt = null,
        private readonly ?SessionService $session = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = JsonRequest::body($request);
        $email = JsonRequest::string($data, 'email');
        $password = JsonRequest::string($data, 'password');
        if ($email === null || $password === null) {
            return JsonResponse::error($response, 'INVALID_CREDENTIALS', 401);
        }

        $user = ($this->users ?? new UserRepository())->verifyPassword($email, $password);
        if ($user === null || !in_array($user['group'] ?? null, ['admin', 'user'], true)) {
            return JsonResponse::error($response, 'INVALID_CREDENTIALS', 401);
        }

        $token = ($this->jwt ?? new JwtService())->createToken($user['id']);
        ($this->session ?? new SessionService())->setUser($user['id'], $user['group']);

        return JsonResponse::success($response, [
            'user' => $user,
            'token' => $token,
            'group' => $user['group'],
            'requiredPasswordReset' => (bool) ($user['required_password_reset'] ?? false),
        ]);
    }
}
