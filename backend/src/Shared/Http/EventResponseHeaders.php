<?php

declare(strict_types=1);

namespace App\Shared\Http;

use App\Shared\Events\EventRepository;
use Psr\Http\Message\ResponseInterface;

final class EventResponseHeaders
{
    public static function add(ResponseInterface $response, EventRepository $events): ResponseInterface
    {
        try {
            $values = $events->all();
        } catch (\Throwable) {
            return $response;
        }

        $headers = [];
        foreach ($values as $key => $value) {
            $header = self::headerName($key);
            $response = $response->withHeader($header, (string) $value);
            $headers[] = $header;
        }

        if ($headers !== []) {
            $response = $response->withHeader('Access-Control-Expose-Headers', implode(', ', $headers));
        }

        return $response;
    }

    public static function headerName(string $key): string
    {
        return 'X-Event-' . preg_replace('/[^A-Za-z0-9-]/', '-', $key);
    }
}
