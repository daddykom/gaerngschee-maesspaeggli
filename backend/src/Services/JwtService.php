<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class JwtService
{
    private const DEFAULT_ALGORITHM = 'HS256';
    private const DEFAULT_TTL = 3600;

    public function createToken(string $userId): string
    {
        $issuedAt = time();
        $payload = [
            'sub' => $userId,
            'iat' => $issuedAt,
            'exp' => $issuedAt + $this->getTtl(),
        ];

        return JWT::encode($payload, $this->getSecret(), $this->getAlgorithm());
    }

    public function verifyToken(string $token): ?array
    {
        try {
            $payload = JWT::decode(
                $token,
                new Key($this->getSecret(), $this->getAlgorithm()),
            );
        } catch (Throwable) {
            return null;
        }

        return (array) $payload;
    }

    public function getUserIdFromToken(string $token): ?string
    {
        $payload = $this->verifyToken($token);
        $userId = $payload['sub'] ?? null;

        return is_string($userId) && $userId !== '' ? $userId : null;
    }

    public function getBearerToken(ServerRequestInterface $request): ?string
    {
        $authorization = trim($request->getHeaderLine('Authorization'));
        if (preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private function getSecret(): string
    {
        $secret = getenv('JWT_SECRET');
        if ($secret === false || $secret === '') {
            throw new \RuntimeException('JWT_SECRET is not configured.');
        }

        if (strlen($secret) < 32) {
            throw new \RuntimeException('JWT_SECRET must contain at least 32 bytes.');
        }

        return $secret;
    }

    private function getAlgorithm(): string
    {
        return getenv('JWT_ALGORITHM') ?: self::DEFAULT_ALGORITHM;
    }

    private function getTtl(): int
    {
        $ttl = filter_var(getenv('JWT_TTL'), FILTER_VALIDATE_INT);

        return is_int($ttl) && $ttl > 0 ? $ttl : self::DEFAULT_TTL;
    }
}
