<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Auth\Services\SessionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class CsrfMiddleware
{
    /** @var list<string> */
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
    ): ResponseInterface {
        $session = new SessionService();
        $csrfToken = $session->getCsrfToken();

        if (!in_array(strtoupper($request->getMethod()), self::SAFE_METHODS, true)
            && $session->hasUserSession()
            && !$session->isCsrfTokenValid($request->getHeaderLine('X-CSRF-Token'))
        ) {
            return $this->jsonError();
        }

        $response = $handler->handle($request);
        $cookie = 'XSRF-TOKEN=' . $csrfToken . '; Path=/; SameSite=Lax';
        if (getenv('APP_ENV') === 'prod') {
            $cookie .= '; Secure';
        }

        return $response->withAddedHeader('Set-Cookie', $cookie);
    }

    private function jsonError(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'error' => [
                'code' => 'CSRF_TOKEN_INVALID',
                'details' => [],
            ],
        ], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(403);
    }
}
