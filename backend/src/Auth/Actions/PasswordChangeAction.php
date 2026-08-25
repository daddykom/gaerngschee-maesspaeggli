<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Auth\Services\AccessKeyService;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PasswordChangeAction
{
    public function __construct(private readonly ?AccessKeyService $accessKeys = null)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = JsonRequest::body($request);
        $accessKey = JsonRequest::string($data, 'accessKey');
        $password = JsonRequest::string($data, 'password');
        if ($accessKey === null || $password === null) {
            return JsonResponse::error($response, 'INVALID_PASSWORD', 422);
        }

        $user = ($this->accessKeys ?? new AccessKeyService())->resetPassword($accessKey, $password);

        return $user === null
            ? JsonResponse::error($response, 'INVALID_ACCESS_KEY', 401)
            : JsonResponse::success($response, ['user' => $user]);
    }
}
