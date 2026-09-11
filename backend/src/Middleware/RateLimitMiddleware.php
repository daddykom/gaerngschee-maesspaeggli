<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Shared\Http\JsonResponse;
use App\Shared\Http\RateLimitService;
use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RateLimitMiddleware implements MiddlewareInterface
{
    /** @param Closure(ServerRequestInterface): string $keyResolver */
    public function __construct(
        private readonly int $limit,
        private readonly int $windowSeconds,
        private readonly Closure $keyResolver,
        private readonly ?RateLimitService $rateLimits = null,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $retryAfter = ($this->rateLimits ?? new RateLimitService())->consume(
            ($this->keyResolver)($request),
            $this->limit,
            $this->windowSeconds,
        );
        if ($retryAfter !== null) {
            return JsonResponse::error(new \Slim\Psr7\Response(), 'RATE_LIMITED', 429)
                ->withHeader('Retry-After', (string) $retryAfter);
        }

        return $handler->handle($request);
    }

    public static function ip(ServerRequestInterface $request): string
    {
        return 'ip:' . ($request->getServerParams()['REMOTE_ADDR'] ?? 'unknown');
    }

    public static function ipAndEmail(ServerRequestInterface $request): string
    {
        $body = $request->getParsedBody();
        $email = is_array($body) && isset($body['email']) ? strtolower(trim((string) $body['email'])) : '';

        return self::ip($request) . '|email:' . $email;
    }

    public static function session(ServerRequestInterface $request): string
    {
        return self::ip($request) . '|session:' . ($request->getCookieParams()['PHPSESSID'] ?? 'unknown');
    }
}
