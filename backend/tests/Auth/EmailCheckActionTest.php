<?php

declare(strict_types=1);

namespace Tests\Auth;

use App\Auth\Actions\EmailCheckAction;
use App\Configuration\Data\FrontendConfigRepository;
use App\Registration\Data\OrderRepository;
use App\Registration\Services\AnmeldungService;
use App\Registration\Services\RegistrationTokenService;
use App\Users\Data\UserRepository;
use Tests\Support\RecordingEmailSender;
use Tests\Support\TestDatabase;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Response;
use Slim\Psr7\Stream;

final class EmailCheckActionTest extends TestCase
{
    public function testAdminEmailRequiresPasswordWithoutCheckingOrders(): void
    {
        $pdo = TestDatabase::create();
        $users = new UserRepository($pdo);
        $users->createUser('admin@example.com', 'secret', 'admin');
        $sender = new RecordingEmailSender();

        $response = $this->action($pdo, $sender)->__invoke(
            $this->request('admin@example.com'),
            new Response(),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['mode' => 'password'], $this->json($response));
        self::assertSame([], $sender->recipients);
    }

    #[DataProvider('editableOrderStatuses')]
    public function testClientWithEditableOrderReceivesExistingRegistrationLink(string $status): void
    {
        $pdo = TestDatabase::create();
        $users = new UserRepository($pdo);
        $user = $users->createUser('client@example.com', 'secret', 'client');
        $this->configureCampaignYear($pdo, 2026);
        $pdo->prepare(
            'INSERT INTO orders (id, user_id, year, status) VALUES (:id, :user_id, :year, :status)',
        )->execute([
            'id' => 'order-1',
            'user_id' => $user['id'],
            'year' => 2026,
            'status' => $status,
        ]);
        $sender = new RecordingEmailSender();

        $response = $this->action($pdo, $sender)->__invoke(
            $this->request('client@example.com'),
            new Response(),
        );

        self::assertSame(202, $response->getStatusCode());
        self::assertSame('email_sent', $this->json($response)['mode']);
        self::assertSame(['client@example.com'], $sender->recipients);
        self::assertStringStartsWith('http://localhost:4200/client-login?token=', $sender->loginUrls[0]);
        self::assertSame(1, (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn());
    }

    public static function editableOrderStatuses(): iterable
    {
        yield 'provisional' => ['provisional'];
        yield 'definitive' => ['definitive'];
    }

    public function testOtherCasesReturnInformationWithoutSendingEmail(): void
    {
        $pdo = TestDatabase::create();
        $users = new UserRepository($pdo);
        $user = $users->createUser('client@example.com', 'secret', 'client');
        $this->configureCampaignYear($pdo, 2026);
        $pdo->prepare(
            'INSERT INTO orders (id, user_id, year, status) VALUES (:id, :user_id, :year, :status)',
        )->execute([
            'id' => 'order-1',
            'user_id' => $user['id'],
            'year' => 2026,
            'status' => 'delivered',
        ]);
        $sender = new RecordingEmailSender();

        $response = $this->action($pdo, $sender)->__invoke(
            $this->request('client@example.com'),
            new Response(),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['mode' => 'info', 'processingYear' => 2026], $this->json($response));
        self::assertSame([], $sender->recipients);
    }

    private function action(\PDO $pdo, RecordingEmailSender $sender): EmailCheckAction
    {
        return new EmailCheckAction(
            new UserRepository($pdo),
            new FrontendConfigRepository($pdo),
            new OrderRepository($pdo),
            new RegistrationTokenService($pdo),
            new AnmeldungService($sender),
        );
    }

    private function configureCampaignYear(\PDO $pdo, int $year): void
    {
        $pdo->prepare(
            'INSERT INTO frontend_config (id, variable_name, value, access_group, update_group, label) '
            . 'VALUES (:id, :variable_name, :value, :access_group, :update_group, :label)',
        )->execute([
            'id' => 'campaign-year',
            'variable_name' => 'campaign_year',
            'value' => json_encode((string) $year, JSON_THROW_ON_ERROR),
            'access_group' => '["admin"]',
            'update_group' => '["admin"]',
            'label' => 'Campaign year',
        ]);
    }

    private function request(string $email): \Psr\Http\Message\ServerRequestInterface
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, json_encode(['email' => $email], JSON_THROW_ON_ERROR));
        rewind($stream);

        return (new ServerRequestFactory())->createServerRequest('POST', '/auth/email-check')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new Stream($stream));
    }

    /** @return array<string, mixed> */
    private function json(Response $response): array
    {
        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
