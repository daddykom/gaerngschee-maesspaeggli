<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Data\UserRepository;
use App\Services\AnmeldungService;
use App\Services\EmailSenderInterface;
use App\Services\AnmeldungMailVariant;
use App\Services\FairgateContactProvider;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AnmeldungServiceTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $users;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->exec(
            'CREATE TABLE users (
                id TEXT PRIMARY KEY,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                "group" TEXT NOT NULL,
                created_at TEXT,
                updated_at TEXT
            )',
        );
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

final class RecordingEmailSender implements EmailSenderInterface
{
    /** @var list<string> */
    public array $recipients = [];
    /** @var list<AnmeldungMailVariant> */
    public array $variants = [];

    public function sendAnmeldung(string $recipient, AnmeldungMailVariant $variant, string $locale = 'de'): void
    {
        $this->recipients[] = $recipient;
        $this->variants[] = $variant;
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
}
