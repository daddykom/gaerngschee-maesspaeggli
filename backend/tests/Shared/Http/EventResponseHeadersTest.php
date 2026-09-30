<?php

declare(strict_types=1);

namespace Tests\Shared\Http;

use App\Shared\Events\EventRepository;
use App\Shared\Http\EventResponseHeaders;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Response;
use Tests\Support\TestDatabase;

final class EventResponseHeadersTest extends TestCase
{
    public function testAddsEventValuesAndExposeHeaders(): void
    {
        $pdo = TestDatabase::create();
        $events = new EventRepository($pdo);
        $events->increment('order-status-change', 7);

        $response = EventResponseHeaders::add(new Response(), $events);

        self::assertSame('7', $response->getHeaderLine('X-Event-order-status-change'));
        self::assertSame('X-Event-order-status-change', $response->getHeaderLine('Access-Control-Expose-Headers'));
    }
}
