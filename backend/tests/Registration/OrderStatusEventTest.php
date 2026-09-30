<?php

declare(strict_types=1);

namespace Tests\Registration;

use App\Registration\Data\OrderRepository;
use App\Shared\Events\EventRepository;
use App\Users\Data\UserRepository;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

final class OrderStatusEventTest extends TestCase
{
    public function testIncrementsOnceForEveryActualOrderStatusChange(): void
    {
        $pdo = TestDatabase::create();
        $events = new EventRepository($pdo);
        $orders = new OrderRepository($pdo, $events);
        $user = (new UserRepository($pdo))->createUser('events@example.com', 'secret', 'client');

        $order = $orders->saveForYear($user['id'], 2026, 'provisional', 1, 0, [
            ['personType' => 'adult', 'category' => 'catA', 'quantity' => 1],
        ]);
        self::assertSame(1, $events->all()['order-status-change']);

        $orders->saveForYear($user['id'], 2026, 'provisional', 1, 0, [
            ['personType' => 'adult', 'category' => 'catA', 'quantity' => 1],
        ]);
        self::assertSame(1, $events->all()['order-status-change']);

        $orders->saveForYear($user['id'], 2026, 'definitive', 1, 0, [
            ['personType' => 'adult', 'category' => 'catA', 'quantity' => 1],
        ]);
        self::assertSame(2, $events->all()['order-status-change']);

        self::assertSame(1, $orders->markDefinitiveForDelivery(2026));
        self::assertSame(3, $events->all()['order-status-change']);
        $orders->setDeliveryToken($order['id'], 'delivery-token');
        $orders->markQrCodeSent($order['id']);
        self::assertSame(4, $events->all()['order-status-change']);
        self::assertTrue($orders->markDelivered($order['id']));
        self::assertSame(5, $events->all()['order-status-change']);
        self::assertTrue($orders->undoDelivery($order['id']));
        self::assertSame(6, $events->all()['order-status-change']);
    }

    public function testBatchConfirmationChangesProvisionalToDefinitive(): void
    {
        $pdo = TestDatabase::create();
        $events = new EventRepository($pdo);
        $orders = new OrderRepository($pdo, $events);
        $user = (new UserRepository($pdo))->createUser('batch-events@example.com', 'secret', 'client');
        $order = $orders->saveForYear($user['id'], 2026, 'provisional', 1, 0, []);

        $orders->markBatchEmailSent($order['id']);

        self::assertSame('definitive', $orders->findForYear($user['id'], 2026)['status']);
        self::assertSame(2, $events->all()['order-status-change']);
    }
}
