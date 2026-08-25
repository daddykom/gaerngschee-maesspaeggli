<?php

declare(strict_types=1);

namespace Tests\Registration;

use App\Users\Data\UserRepository;
use App\Registration\Services\AnmeldungService;
use App\Registration\Services\AnmeldungMailVariant;
use App\Fairgate\Services\FairgateContactProvider;
use Tests\Support\RecordingEmailSender;
use Tests\Support\TestDatabase;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AnmeldungServiceTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $users;

    protected function setUp(): void
    {
        $this->pdo = TestDatabase::create();
        $this->users = new UserRepository($this->pdo);
    }

    #[DataProvider('emailStates')]
    public function testInformationEmailIsSentForEveryValidState(bool $userExists, bool $fairgateExists): void
    {
        $email = sprintf('person-%s@example.com', uniqid());
        if ($userExists) {
            $this->users->createUser($email, 'secret');
        }

        $sender = new RecordingEmailSender();
        $service = new AnmeldungService(
            $this->users,
            new StubFairgateContactProvider($fairgateExists),
            $sender,
        );

        $service->sendInformationEmail($email);

        self::assertSame([$email], $sender->recipients);
        self::assertSame([AnmeldungMailVariant::fromChecks($userExists, $fairgateExists)], $sender->variants);
    }

    public static function emailStates(): array
    {
        return [[false, false], [false, true], [true, false], [true, true]];
    }

    public function testInvalidEmailIsRejectedBeforeChecksOrSending(): void
    {
        $sender = new RecordingEmailSender();
        $service = new AnmeldungService($this->users, new StubFairgateContactProvider(false), $sender);

        $this->expectException(\InvalidArgumentException::class);
        $service->sendInformationEmail('invalid');
        self::assertSame([], $sender->recipients);
    }
}

final class StubFairgateContactProvider implements FairgateContactProvider
{
    public function __construct(private readonly bool $exists)
    {
    }

    public function hasContactByEmail(string $email): bool
    {
        return $this->exists;
    }

    public function findContactDataByEmail(string $email): array
    {
        return $this->exists ? ['success' => true, 'data' => []] : ['success' => true, 'data' => null];
    }
}
