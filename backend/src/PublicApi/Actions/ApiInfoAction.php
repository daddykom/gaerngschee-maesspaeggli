<?php

declare(strict_types=1);

namespace App\PublicApi\Actions;

use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ApiInfoAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponse::success($response, ['message' => 'API']);
    }
}
