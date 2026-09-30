<?php

declare(strict_types=1);

namespace Tests\Shared\Events;

use App\Shared\Events\EventRepository;
use PHPUnit\Framework\TestCase;
use Tests\Support\TestDatabase;

final class EventRepositoryTest extends TestCase
{
    public function testCreatesAndIncrementsEvents(): void
    {
        $events = new EventRepository(TestDatabase::create());

        self::assertSame(1, $events->increment('order-status-change'));
        self::assertSame(4, $events->increment('order-status-change', 3));
        self::assertSame(['order-status-change' => 4], $events->all());
    }

    public function testRejectsInvalidIncrement(): void
    {
        $events = new EventRepository(TestDatabase::create());

        $this->expectException(\InvalidArgumentException::class);
        $events->increment('', 0);
    }
}
