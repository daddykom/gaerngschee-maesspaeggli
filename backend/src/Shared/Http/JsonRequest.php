<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Psr\Http\Message\ServerRequestInterface;

final class JsonRequest
{
    public const MAX_BODY_BYTES = 65536;

    public static function body(ServerRequestInterface $request): array
    {
        $contentLength = $request->getHeaderLine('Content-Length');
        if (($contentLength !== '' && (int) $contentLength > self::MAX_BODY_BYTES)
            || (($size = $request->getBody()->getSize()) !== null && $size > self::MAX_BODY_BYTES)) {
            throw new \LengthException('Request body exceeds the maximum size.');
        }

        $data = json_decode((string) $request->getBody(), true);

        return is_array($data) ? $data : [];
    }

    public static function string(array $data, string $key, int $maxLength = 2048): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) && $value !== '' && strlen($value) <= $maxLength ? $value : null;
    }
}
