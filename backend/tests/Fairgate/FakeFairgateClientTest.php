<?php

declare(strict_types=1);

namespace Tests\Fairgate;

use App\Fairgate\Services\FakeFairgateClient;
use PHPUnit\Framework\TestCase;

final class FakeFairgateClientTest extends TestCase
{
    public function testFairMarkerAfterPlusReturnsTrue(): void
    {
        $client = new FakeFairgateClient();

        self::assertTrue($client->hasContactByEmail('person+fair@example.com'));
        self::assertTrue($client->hasContactByEmail('person+FAIR-test@example.com'));
    }

    public function testMissingOrDifferentMarkerReturnsFalse(): void
    {
        $client = new FakeFairgateClient();

        self::assertFalse($client->hasContactByEmail('person@example.com'));
        self::assertFalse($client->hasContactByEmail('person+test@example.com'));
    }
}
