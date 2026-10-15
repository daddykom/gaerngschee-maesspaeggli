<?php

declare(strict_types=1);

use App\Configuration\Environment;
use App\Fairgate\Services\FairgateBatchContactProvider;
use App\Fairgate\Services\FairgateContactProviderFactory;
use App\Fairgate\Services\FairgateOrderDataEvaluator;
use App\Registration\Data\OrderRepository;
use App\Shared\Database\Database;

require __DIR__ . '/../vendor/autoload.php';
Environment::load();

$options = getopt('', ['year::', 'output:']);
$year = null;
if (array_key_exists('year', $options)) {
    $yearValue = $options['year'];
    if (!is_string($yearValue) || preg_match('/^\d{4}$/', $yearValue) !== 1) {
        fwrite(STDERR, "The --year option must be a four-digit year.\n");
        exit(1);
    }
    $year = (int) $yearValue;
}

$output = $options['output'] ?? null;
if (!is_string($output) || trim($output) === '') {
    fwrite(STDERR, "The --output option is required.\n");
    exit(1);
}

try {
    $orders = (new OrderRepository(Database::getConnection()))->findAllForFairgatePersonCountCheck($year);
    $fairgate = FairgateContactProviderFactory::create();
    $evaluator = new FairgateOrderDataEvaluator();
    $contacts = $fairgate instanceof FairgateBatchContactProvider
        ? contactMap($fairgate->findAllContacts())
        : null;

    $handle = fopen($output, 'wb');
    if ($handle === false) {
        throw new RuntimeException(sprintf('Could not open output file: %s', $output));
    }

    fputcsv($handle, [
        'order_id',
        'email',
        'year',
        'order_status',
        'stored_fairgate_status',
        'fairgate_status',
        'stored_adults',
        'expected_adults',
        'stored_children',
        'expected_children',
        'reason',
    ], ',', '"', '\\');

    $differences = 0;
    foreach ($orders as $order) {
        $contactId = $contacts[strtolower(trim($order['email']))] ?? null;
        $data = $contactId === null
            ? ($contacts === null ? ($fairgate->findContactDataByEmail($order['email'])['data'] ?? null) : null)
            : ($fairgate->findContactDataById($contactId)['data'] ?? null);

        if (!is_array($data)) {
            writeDifference($handle, $order, null, null, 'fairgate_not_found');
            $differences++;
            continue;
        }

        $createdAt = is_string($order['createdAt']) && trim($order['createdAt']) !== ''
            ? new DateTimeImmutable($order['createdAt'], new DateTimeZone('UTC'))
            : new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $evaluation = $evaluator->evaluate($data, $createdAt, $order['year']);
        $countDifference = $order['adultsCount'] !== $evaluation['adultsCount']
            || $order['childrenCount'] !== $evaluation['childrenCount'];
        $statusDifference = $evaluation['status'] !== 'valid';
        if (!$countDifference && !$statusDifference) {
            continue;
        }

        $reason = $countDifference && $statusDifference
            ? 'count_and_fairgate_status'
            : ($countDifference ? 'count_difference' : 'fairgate_status_not_valid');
        writeDifference($handle, $order, $evaluation['status'], $evaluation, $reason);
        $differences++;
    }
    fclose($handle);

    printf("Orders checked: %d, differences written: %d, output: %s\n", count($orders), $differences, $output);
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Order person count check failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

/** @param list<array{email: string, contactId: string}> $contacts */
function contactMap(array $contacts): array
{
    $map = [];
    foreach ($contacts as $contact) {
        $map[strtolower(trim($contact['email']))] = $contact['contactId'];
    }
    return $map;
}

/** @param array<string, mixed> $order @param array<string, mixed>|null $evaluation */
function writeDifference($handle, array $order, ?string $fairgateStatus, ?array $evaluation, string $reason): void
{
    fputcsv($handle, [
        $order['orderId'],
        $order['email'],
        $order['year'],
        $order['status'],
        $order['fairgateStatus'] ?? '',
        $fairgateStatus ?? '',
        $order['adultsCount'],
        $evaluation['adultsCount'] ?? '',
        $order['childrenCount'],
        $evaluation['childrenCount'] ?? '',
        $reason,
    ], ',', '"', '\\');
}
