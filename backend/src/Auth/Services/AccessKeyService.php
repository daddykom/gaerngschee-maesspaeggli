<?php

declare(strict_types=1);

namespace App\Auth\Services;

use App\Auth\Data\AccessKeyRepository;
use App\Shared\Database\Database;
use App\Users\Data\UserRepository;
use DateTimeImmutable;
use PDO;
use Throwable;

final class AccessKeyService
{
    public const PASSWORD_RESET = 'password_reset';
    public const CLIENT_LOGIN = 'client_login';

    private readonly PDO $pdo;
    private readonly UserRepository $userRepository;
    private readonly AccessKeyRepository $accessKeyRepository;

    public function __construct(
        ?PDO $pdo = null,
        ?UserRepository $userRepository = null,
        ?AccessKeyRepository $accessKeyRepository = null,
    ) {
        $this->pdo = $pdo ?? Database::getConnection();
        $this->userRepository = $userRepository ?? new UserRepository($this->pdo);
        $this->accessKeyRepository = $accessKeyRepository ?? new AccessKeyRepository($this->pdo);
    }

    public function generateForUser(
        string $userId,
        string $purpose,
        ?DateTimeImmutable $now = null,
    ): ?array {
        $this->assertPurpose($purpose);
        $now ??= new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $user = $this->userRepository->findById($userId);

        if ($user === null || ($purpose === self::CLIENT_LOGIN && $user['group'] !== 'client')) {
            return null;
        }

        $accessKey = $this->createAccessKey();
        $expiresAt = $now->modify('+10 minutes');

        $this->pdo->beginTransaction();
        try {
            $this->accessKeyRepository->invalidateActiveForUserAndPurpose($userId, $purpose);
            $this->accessKeyRepository->create(
                $userId,
                $purpose,
                $this->hashAccessKey($accessKey),
                $expiresAt,
            );
            $this->pdo->commit();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return [
            'accessKey' => $accessKey,
            'expiresAt' => $expiresAt->format(DATE_ATOM),
            'purpose' => $purpose,
            'group' => $user['group'],
        ];
    }

    public function resetPassword(
        string $accessKey,
        string $password,
        ?DateTimeImmutable $now = null,
    ): ?array {
        $now ??= new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->pdo->beginTransaction();

        try {
            $record = $this->accessKeyRepository->findValid(
                $this->hashAccessKey($accessKey),
                self::PASSWORD_RESET,
                $now,
            );
            if ($record === null || !$this->accessKeyRepository->consume($record['id'], $now)) {
                $this->pdo->rollBack();
                return null;
            }

            $user = $this->userRepository->updatePassword($record['user_id'], $password);
            $this->pdo->commit();

            return $user;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function loginClient(
        string $accessKey,
        ?DateTimeImmutable $now = null,
    ): ?array {
        $now ??= new DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->pdo->beginTransaction();

        try {
            $record = $this->accessKeyRepository->findValid(
                $this->hashAccessKey($accessKey),
                self::CLIENT_LOGIN,
                $now,
            );
            $user = $record === null ? null : $this->userRepository->findById($record['user_id']);

            if (
                $record === null
                || $user === null
                || ($user['group'] ?? null) !== 'client'
                || !$this->accessKeyRepository->consume($record['id'], $now)
            ) {
                $this->pdo->rollBack();
                return null;
            }

            $this->pdo->commit();

            return $user;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    private function assertPurpose(string $purpose): void
    {
        if (!in_array($purpose, [self::PASSWORD_RESET, self::CLIENT_LOGIN], true)) {
            throw new \InvalidArgumentException('Invalid access key purpose.');
        }
    }

    private function createAccessKey(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function hashAccessKey(string $accessKey): string
    {
        return hash('sha256', $accessKey);
    }
}
