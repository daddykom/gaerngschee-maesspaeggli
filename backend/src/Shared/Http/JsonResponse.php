<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Psr\Http\Message\ResponseInterface;

final class JsonResponse
{
    public static function success(ResponseInterface $response, mixed $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    public static function error(ResponseInterface $response, string $code, int $status): ResponseInterface
    {
        return self::success($response, ['error' => ['code' => $code, 'details' => []]], $status);
    }
}
