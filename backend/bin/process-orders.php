<?php

declare(strict_types=1);

use App\Configuration\Data\FrontendConfigRepository;
use App\Fairgate\Services\FairgateContactProviderFactory;
use App\Registration\Data\OrderEmailQueueRepository;
use App\Registration\Data\OrderRepository;
use App\Registration\Data\RegistrationTokenRepository;
use App\Registration\Services\OrderBatchService;
use App\Shared\Database\Database;
use App\Shared\Mail\EmailSender;

require __DIR__ . '/../vendor/autoload.php';

try {
    $pdo = Database::getConnection();
    $service = new OrderBatchService(
        new OrderRepository($pdo),
        new OrderEmailQueueRepository($pdo),
        new FrontendConfigRepository($pdo),
        FairgateContactProviderFactory::create(),
        new EmailSender(),
        new RegistrationTokenRepository($pdo),
    );
    $result = $service->run();
    printf(
        "Orders loaded: %d, emails sent: %d, queued: %d, failed: %d, registration tokens deleted: %d\n",
        $result['loaded'],
        $result['sent'],
        $result['queued'],
        $result['failed'],
        $result['tokensDeleted'],
    );
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Order batch initialization failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
