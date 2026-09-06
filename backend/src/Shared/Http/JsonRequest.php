<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Psr\Http\Message\ServerRequestInterface;

final class JsonRequest
{
    public static function body(ServerRequestInterface $request): array
    {
        $data = json_decode((string) $request->getBody(), true);

        return is_array($data) ? $data : [];
    }

    public static function string(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
