<?php

declare(strict_types=1);

namespace Tests\Shared\Http;

use App\Shared\Http\JsonRequest;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Stream;

final class JsonRequestTest extends TestCase
{
    public function testRejectsBodiesLargerThanTheConfiguredLimit(): void
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, str_repeat('x', JsonRequest::MAX_BODY_BYTES + 1));
        rewind($stream);
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/test')
            ->withBody(new Stream($stream));

        self::expectException(\LengthException::class);
        JsonRequest::body($request);
    }

    public function testRejectsStringsLongerThanTheConfiguredLimit(): void
    {
        self::assertNull(JsonRequest::string(['value' => str_repeat('x', 129)], 'value', 128));
        self::assertSame('valid', JsonRequest::string(['value' => 'valid'], 'value', 128));
    }
}
