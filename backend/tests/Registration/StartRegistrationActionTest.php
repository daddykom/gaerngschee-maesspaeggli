<?php

declare(strict_types=1);

namespace Tests\Registration;

use App\Registration\Actions\StartRegistrationAction;
use App\Registration\Services\AnmeldungService;
use App\Fairgate\Services\FairgateContactProvider;
use App\Shared\Mail\EmailSenderInterface;
use App\Users\Data\UserRepository;
use Tests\Support\TestDatabase;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

final class StartRegistrationActionTest extends TestCase
{
    public function testAcceptsValidEmailAndLanguage(): void
    {
        $action = new StartRegistrationAction($this->service());

        $response = ($action)($this->request('person@example.com', 'de'), new Response());

        self::assertSame(202, $response->getStatusCode());
        self::assertSame(['sent' => true], json_decode((string) $response->getBody(), true));
    }

    public function testRejectsUnsupportedLanguageBeforeCallingService(): void
    {
        $action = new StartRegistrationAction($this->service());

        $response = ($action)($this->request('person@example.com', 'fr'), new Response());

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('UNSUPPORTED_LANGUAGE', json_decode((string) $response->getBody(), true)['error']['code']);
    }

    private function service(): AnmeldungService
    {
        $pdo = TestDatabase::create();

        return new AnmeldungService(
            new UserRepository($pdo),
            new class () implements FairgateContactProvider {
                public function hasContactByEmail(string $email): bool { return false; }
            },
            new class () implements EmailSenderInterface {
                public function sendAnmeldung(string $recipient, \App\Registration\Services\AnmeldungMailVariant $variant, string $locale = 'de'): void {}
                public function sendUserCreated(string $recipient, string $temporaryPassword): void {}
                public function sendUserEmailChanged(string $recipient): void {}
            },
        );
    }

    private function request(string $email, string $language): \Psr\Http\Message\ServerRequestInterface
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, json_encode(['email' => $email, 'language' => $language], JSON_THROW_ON_ERROR));
        rewind($stream);

        return (new \Slim\Psr7\Factory\ServerRequestFactory())
            ->createServerRequest('POST', '/public/start')
            ->withBody(new Stream($stream));
    }
}
