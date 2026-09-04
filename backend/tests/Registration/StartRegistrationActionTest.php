<?php

declare(strict_types=1);

namespace Tests\Registration;

use App\Registration\Actions\StartRegistrationAction;
use App\Registration\Services\AnmeldungService;
use App\Registration\Services\RegistrationTokenService;
use App\Shared\Mail\EmailSenderInterface;
use Tests\Support\TestDatabase;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

final class StartRegistrationActionTest extends TestCase
{
    public function testAcceptsValidEmailAndLanguage(): void
    {
        $action = new StartRegistrationAction($this->service(), new RegistrationTokenService(TestDatabase::create()));

        $response = ($action)($this->request('person@example.com', 'de'), new Response());

        self::assertSame(202, $response->getStatusCode());
        self::assertSame(['sent' => true], json_decode((string) $response->getBody(), true));
    }

    public function testRejectsUnsupportedLanguageBeforeCallingService(): void
    {
        $action = new StartRegistrationAction($this->service(), new RegistrationTokenService(TestDatabase::create()));

        $response = ($action)($this->request('person@example.com', 'fr'), new Response());

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('UNSUPPORTED_LANGUAGE', json_decode((string) $response->getBody(), true)['error']['code']);
    }

    private function service(): AnmeldungService
    {
        return new AnmeldungService(
            new class () implements EmailSenderInterface {
                public function sendAnmeldung(string $recipient, \App\Registration\Services\AnmeldungMailVariant $variant, string $locale = 'de', ?string $loginUrl = null): void {}
                public function sendUserCreated(string $recipient, string $temporaryPassword): void {}
                public function sendUserEmailChanged(string $recipient): void {}
                public function sendOrderConfirmation(string $recipient, array $order): void {}
                public function renderOrderConfirmation(array $order): array { return []; }
                public function sendStoredEmail(string $recipient, string $subject, string $html, string $text): void {}
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
