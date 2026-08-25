<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Auth\Services\AccessKeyService;
use App\Auth\Services\JwtService;
use App\Auth\Services\SessionService;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ClientLoginAction
{
    public function __construct(
        private readonly ?AccessKeyService $accessKeys = null,
        private readonly ?JwtService $jwt = null,
        private readonly ?SessionService $session = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $accessKey = JsonRequest::string(JsonRequest::body($request), 'accessKey');
        if ($accessKey === null) {
            return JsonResponse::error($response, 'INVALID_ACCESS_KEY', 401);
        }

        $user = ($this->accessKeys ?? new AccessKeyService())->loginClient($accessKey);
        if ($user === null) {
            return JsonResponse::error($response, 'INVALID_ACCESS_KEY', 401);
        }

        $token = ($this->jwt ?? new JwtService())->createToken($user['id']);
        ($this->session ?? new SessionService())->setUser($user['id'], $user['group']);

        return JsonResponse::success($response, ['user' => $user, 'token' => $token, 'group' => $user['group']]);
    }
}
