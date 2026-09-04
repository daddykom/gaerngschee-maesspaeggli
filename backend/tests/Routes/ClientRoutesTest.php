<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Auth\Services\SessionService;
use App\Registration\Data\OrderRepository;
use App\Routes\ClientRoutes;
use App\Users\Data\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\Support\TestDatabase;

final class ClientRoutesTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $users;
    private OrderRepository $orders;

    protected function setUp(): void
    {
        (new SessionService())->clear();
        $this->pdo = TestDatabase::create();
        $this->users = new UserRepository($this->pdo);
        $this->orders = new OrderRepository($this->pdo);
    }

    protected function tearDown(): void
    {
        (new SessionService())->clear();
    }

    public function testClientCanSaveAndLoadCurrentYearsOrder(): void
    {
        $client = $this->users->createUser('client@example.com', 'secret', 'client');
        (new SessionService())->setUser($client['id'], 'client', true);
        $app = $this->createApp();

        $save = $app->handle($this->request('PUT', [
            'adultsCount' => 2,
            'childrenCount' => 1,
            'adults' => ['catA', 'catA'],
            'children' => ['catB'],
        ]));
        $saved = json_decode((string) $save->getBody(), true, 512, JSON_THROW_ON_ERROR)['order'];

        self::assertSame(200, $save->getStatusCode());
        self::assertSame('definitiv', $saved['status']);
        self::assertSame(2, $saved['adultsCount']);
        self::assertSame(1, $saved['childrenCount']);
        self::assertSame(2, $saved['items'][0]['quantity']);

        $loadedResponse = $app->handle($this->request('GET'));
        $loaded = json_decode((string) $loadedResponse->getBody(), true, 512, JSON_THROW_ON_ERROR)['order'];

        self::assertSame(200, $loadedResponse->getStatusCode());
        self::assertSame($saved['id'], $loaded['id']);
        self::assertSame($saved, $loaded);
    }

    public function testSecondSaveOverwritesTheCurrentYearsOrder(): void
    {
        $client = $this->users->createUser('client@example.com', 'secret', 'client');
        (new SessionService())->setUser($client['id'], 'client', false);
        $app = $this->createApp();

        $first = $app->handle($this->request('PUT', [
            'adultsCount' => 1,
            'childrenCount' => 0,
            'adults' => ['catA'],
            'children' => [],
        ]));
        $firstOrder = json_decode((string) $first->getBody(), true, 512, JSON_THROW_ON_ERROR)['order'];

        $second = $app->handle($this->request('PUT', [
            'adultsCount' => 1,
            'childrenCount' => 0,
            'adults' => ['catG'],
            'children' => [],
        ]));
        $secondOrder = json_decode((string) $second->getBody(), true, 512, JSON_THROW_ON_ERROR)['order'];

        self::assertSame($firstOrder['id'], $secondOrder['id']);
        self::assertSame('provisorisch', $secondOrder['status']);
        self::assertSame('catG', $secondOrder['items'][0]['category']);
        self::assertSame(1, (int) $this->pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn());
        self::assertSame(1, (int) $this->pdo->query('SELECT COUNT(*) FROM order_items')->fetchColumn());
    }

    public function testUnauthenticatedRequestsAreRejected(): void
    {
        $response = $this->createApp()->handle($this->request('GET'));

        self::assertSame(404, $response->getStatusCode());
    }

    public function testInvalidOrderDataIsRejected(): void
    {
        $client = $this->users->createUser('client@example.com', 'secret', 'client');
        (new SessionService())->setUser($client['id'], 'client', true);

        $response = $this->createApp()->handle($this->request('PUT', [
            'adultsCount' => 2,
            'childrenCount' => 0,
            'adults' => ['catA'],
            'children' => [],
            'userId' => 'another-user',
        ]));

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('INVALID_ORDER_DATA', json_decode((string) $response->getBody(), true)['error']['code']);
    }

    private function createApp(): \Slim\App
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        ClientRoutes::register($app, $this->orders, $this->users);

        return $app;
    }

    private function request(string $method, array $body = []): \Psr\Http\Message\ServerRequestInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest($method, '/client/order');
        if ($method !== 'GET') {
            $request->getBody()->write(json_encode($body, JSON_THROW_ON_ERROR));
            $request->getBody()->rewind();
        }

        return $request;
    }
}
