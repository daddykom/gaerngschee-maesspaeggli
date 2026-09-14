<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Auth\Services\SessionService;
use App\Registration\Data\OrderRepository;
use App\Routes\DeliveryRoutes;
use App\Users\Data\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\Support\TestDatabase;

final class DeliveryRoutesTest extends TestCase
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

    public function testUnauthenticatedDeliveryRequestIsRejected(): void
    {
        $response = $this->createApp()->handle(
            (new ServerRequestFactory())->createServerRequest('POST', '/delivery/orders/order-1/deliver'),
        );

        self::assertSame(401, $response->getStatusCode());
    }

    public function testUserCanMarkQrCodeOrderAsDelivered(): void
    {
        $user = $this->users->createUser('user@example.com', 'secret', 'user');
        $this->insertOrder('order-1', $user['id'], 'qrcode');
        (new SessionService())->setUser($user['id'], 'user');

        $response = $this->createApp()->handle(
            (new ServerRequestFactory())->createServerRequest('POST', '/delivery/orders/order-1/deliver'),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('delivered', json_decode((string) $response->getBody(), true)['status']);
        self::assertSame('delivered', $this->orderStatus('order-1'));
    }

    public function testUserCanUndoDeliveredOrder(): void
    {
        $user = $this->users->createUser('user@example.com', 'secret', 'user');
        $this->insertOrder('order-1', $user['id'], 'delivered');
        (new SessionService())->setUser($user['id'], 'user');

        $response = $this->createApp()->handle(
            (new ServerRequestFactory())->createServerRequest('POST', '/delivery/orders/order-1/undo'),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('qrcode', json_decode((string) $response->getBody(), true)['status']);
        self::assertSame('qrcode', $this->orderStatus('order-1'));
    }

    /**
     * A delivery operation must not skip a state or repeat an already applied transition.
     */
    public function testInvalidDeliveryTransitionsReturnConflictAndKeepStatus(): void
    {
        $user = $this->users->createUser('user@example.com', 'secret', 'user');
        $otherUser = $this->users->createUser('other@example.com', 'secret', 'user');
        $this->insertOrder('to-deliver', $user['id'], 'toDeliver');
        $this->insertOrder('qrcode', $otherUser['id'], 'qrcode');
        (new SessionService())->setUser($user['id'], 'user');
        $app = $this->createApp();

        $skip = $app->handle(
            (new ServerRequestFactory())->createServerRequest('POST', '/delivery/orders/to-deliver/deliver'),
        );
        $repeat = $app->handle(
            (new ServerRequestFactory())->createServerRequest('POST', '/delivery/orders/qrcode/undo'),
        );

        self::assertSame(409, $skip->getStatusCode());
        self::assertSame(409, $repeat->getStatusCode());
        self::assertSame('DELIVERY_STATUS_INVALID', json_decode((string) $skip->getBody(), true)['error']['code']);
        self::assertSame('toDeliver', $this->orderStatus('to-deliver'));
        self::assertSame('qrcode', $this->orderStatus('qrcode'));
    }

    private function createApp(): \Slim\App
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        DeliveryRoutes::register($app, $this->orders, $this->users);

        return $app;
    }

    private function insertOrder(string $id, string $userId, string $status): void
    {
        $this->pdo->prepare(
            'INSERT INTO orders (id, user_id, year, status, adults_count, children_count)
             VALUES (?, ?, ?, ?, ?, ?)',
        )->execute([$id, $userId, (int) date('Y'), $status, 1, 0]);
    }

    private function orderStatus(string $id): string
    {
        return (string) $this->pdo->query("SELECT status FROM orders WHERE id = '" . $id . "'")->fetchColumn();
    }
}
