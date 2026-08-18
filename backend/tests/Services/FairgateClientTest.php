<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\FairgateClient;
use App\Services\FairgateException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class FairgateClientTest extends TestCase
{
    public function testFindContactByEmailAuthenticatesAndReturnsMinimalContact(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"success":true,"data":{"token":"test-token"}}'),
            new Response(
                200,
                [],
                '{"success":true,"data":{"contacts":[{"basefields":{"contact_id":42},"status":"active","communication":{"primary_email":"person@example.com"}}]}}',
            ),
        ]));
        $handler->push(Middleware::history($history));

        $client = new FairgateClient(
            new Client(['handler' => $handler, 'base_uri' => 'https://fsa-test.fairgate.ch']),
            'https://fsa-test.fairgate.ch',
            'org-123',
            'access-key',
            null,
            static fn (string $token): bool => $token === 'test-token',
        );

        self::assertSame(
            ['id' => '42', 'email' => 'person@example.com', 'status' => 'active'],
            $client->findContactByEmail(' Person@Example.com '),
        );
        self::assertCount(2, $history);
        self::assertSame('/fsa/v1.1/auth/create/org-123/token', $history[0]['request']->getUri()->getPath());
        self::assertSame('/fsa/v1.1/contact/org-123/contacts/list', $history[1]['request']->getUri()->getPath());
        self::assertSame('test-token', $history[1]['request']->getHeaderLine('Authorization'));
    }

    public function testFindContactByEmailReturnsNullWhenContactIsNotFound(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"success":true,"data":{"token":"test-token"}}'),
            new Response(200, [], '{"success":true,"data":{"contacts":[]}}'),
        ]));
        $client = new FairgateClient(
            new Client(['handler' => $handler, 'base_uri' => 'https://fsa-test.fairgate.ch']),
            'https://fsa-test.fairgate.ch',
            'org-123',
            'access-key',
            null,
            static fn (string $token): bool => true,
        );

        self::assertNull($client->findContactByEmail('missing@example.com'));
    }

    public function testInvalidAuthenticationResponseThrowsFairgateException(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(401, [], '{"success":false}'),
        ]));
        $client = new FairgateClient(
            new Client(['handler' => $handler, 'base_uri' => 'https://fsa-test.fairgate.ch']),
            'https://fsa-test.fairgate.ch',
            'org-123',
            'access-key',
            null,
            static fn (string $token): bool => true,
        );

        $this->expectException(FairgateException::class);
        $client->findContactByEmail('person@example.com');
    }
}
