<?php

declare(strict_types=1);

namespace Tests\Registration;

use App\Configuration\Data\FrontendConfigRepository;
use App\Fairgate\Services\FairgateContactProvider;
use App\Registration\Data\OrderEmailQueueRepository;
use App\Registration\Data\OrderRepository;
use App\Registration\Services\OrderBatchService;
use App\Users\Data\UserRepository;
use Tests\Support\RecordingEmailSender;
use Tests\Support\TestDatabase;
use PHPUnit\Framework\TestCase;

final class OrderBatchServiceTest extends TestCase
{
    public function testCorrectsOrderAndMarksItDefinitiveAfterSuccessfulEmail(): void
    {
        $pdo = TestDatabase::create();
        $user = (new UserRepository($pdo))->createUser('person+fair1@example.com', 'secret', 'client');
        $orders = new OrderRepository($pdo);
        $order = $orders->saveForYear($user['id'], 2026, 'provisional', 1, 0, [
            ['personType' => 'adult', 'category' => 'catB', 'quantity' => 1],
        ]);
        $this->addInterval($pdo);
        $emails = new RecordingEmailSender();

        $result = $this->service($pdo, $emails, new FixedFairgateProvider(2, 1))->run();
        $updated = $orders->findForYear($user['id'], 2026);

        self::assertSame(1, $result['loaded']);
        self::assertSame(1, $result['sent']);
        self::assertSame('definitive', $updated['status']);
        self::assertSame(2, $updated['adultsCount']);
        self::assertSame(1, $updated['childrenCount']);
        self::assertSame([
            ['personType' => 'adult', 'category' => 'catA', 'quantity' => 1],
            ['personType' => 'adult', 'category' => 'catB', 'quantity' => 1],
            ['personType' => 'child', 'category' => 'catA', 'quantity' => 1],
        ], $updated['items']);
    }

    public function testQueuesFailedEmailAndLeavesOrderProvisional(): void
    {
        $pdo = TestDatabase::create();
        $user = (new UserRepository($pdo))->createUser('person+fair1@example.com', 'secret', 'client');
        $orders = new OrderRepository($pdo);
        $orders->saveForYear($user['id'], 2026, 'provisional', 1, 0, [
            ['personType' => 'adult', 'category' => 'catA', 'quantity' => 1],
        ]);
        $this->addInterval($pdo);
        $emails = new RecordingEmailSender();
        $emails->failStoredEmail = true;

        $result = $this->service($pdo, $emails, new FixedFairgateProvider(1, 0))->run();

        self::assertSame(1, $result['queued']);
        self::assertSame('provisional', $orders->findForYear($user['id'], 2026)['status']);
        self::assertCount(1, (new OrderEmailQueueRepository($pdo))->pending());
    }

    public function testRetriesQueuedEmailAndDoesNotLoadTheOrderAgain(): void
    {
        $pdo = TestDatabase::create();
        $user = (new UserRepository($pdo))->createUser('person+fair1@example.com', 'secret', 'client');
        $orders = new OrderRepository($pdo);
        $orders->saveForYear($user['id'], 2026, 'provisional', 1, 0, [
            ['personType' => 'adult', 'category' => 'catA', 'quantity' => 1],
        ]);
        $this->addInterval($pdo);
        $emails = new RecordingEmailSender();
        $emails->failStoredEmail = true;
        $service = $this->service($pdo, $emails, new FixedFairgateProvider(1, 0));
        $service->run();

        $emails->failStoredEmail = false;
        $result = $service->run();

        self::assertSame(1, $result['sent']);
        self::assertSame(0, $result['loaded']);
        self::assertSame('definitive', $orders->findForYear($user['id'], 2026)['status']);
        self::assertCount(0, (new OrderEmailQueueRepository($pdo))->pending());
    }

    private function service($pdo, RecordingEmailSender $emails, FairgateContactProvider $fairgate): OrderBatchService
    {
        return new OrderBatchService(
            new OrderRepository($pdo),
            new OrderEmailQueueRepository($pdo),
            new FrontendConfigRepository($pdo),
            $fairgate,
            $emails,
        );
    }

    private function addInterval($pdo): void
    {
        $pdo->prepare(
            'INSERT INTO frontend_config (id, variable_name, value, access_group, update_group, label)
             VALUES (:id, :name, :value, :access, :update, :label)',
        )->execute([
            'id' => 'config-interval',
            'name' => 'fairgate_email_interval_days',
            'value' => json_encode('7', JSON_THROW_ON_ERROR),
            'access' => json_encode(['admin'], JSON_THROW_ON_ERROR),
            'update' => json_encode(['admin'], JSON_THROW_ON_ERROR),
            'label' => 'Interval',
        ]);
    }
}

final class FixedFairgateProvider implements FairgateContactProvider
{
    public function __construct(private readonly int $adults, private readonly int $children)
    {
    }

    public function hasContactByEmail(string $email): bool
    {
        return true;
    }

    public function findContactDataByEmail(string $email): array
    {
        $data = ['wohnt_im_gleichen_haushalt' => $this->adults === 2 ? 'Ja' : 'Nein'];
        for ($index = 1; $index <= $this->children; $index++) {
            $data['name_und_vorname_kind' . $index] = 'Kind ' . $index;
        }
        return ['success' => true, 'data' => $data];
    }
}
