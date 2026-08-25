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
use Throwable;

final class RegisterAction
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
        if ($email === null || $password === null || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return JsonResponse::error($response, 'INVALID_CREDENTIALS', 422);
        }

        $repository = $this->users ?? new UserRepository();
        if ($repository->findByEmail($email) !== null) {
            return JsonResponse::error($response, 'EMAIL_ALREADY_REGISTERED', 409);
        }

        try {
            $user = $repository->createUser($email, $password, 'client');
            $token = ($this->jwt ?? new JwtService())->createToken($user['id']);
            ($this->session ?? new SessionService())->setUserId($user['id']);
        } catch (Throwable) {
            return JsonResponse::error($response, 'REGISTRATION_FAILED', 500);
        }

        return JsonResponse::success($response, ['user' => $user, 'token' => $token], 201);
    }
}
