<?php

declare(strict_types=1);

namespace Tests\Registration;

use App\Registration\Services\AnmeldungService;
use App\Registration\Services\AnmeldungMailVariant;
use Tests\Support\RecordingEmailSender;
use PHPUnit\Framework\TestCase;

final class AnmeldungServiceTest extends TestCase
{
    public function testRegistrationLinkEmailIsSent(): void
    {
        $sender = new RecordingEmailSender();
        $service = new AnmeldungService($sender);

        $service->sendRegistrationLink('person@example.com', 'http://localhost:4200/client-login?token=test');

        self::assertSame(['person@example.com'], $sender->recipients);
        self::assertSame([AnmeldungMailVariant::ClientOrder], $sender->variants);
    }

    public function testInvalidEmailIsRejectedBeforeChecksOrSending(): void
    {
        $sender = new RecordingEmailSender();
        $service = new AnmeldungService($sender);

        $this->expectException(\InvalidArgumentException::class);
        $service->sendRegistrationLink('invalid', 'http://localhost:4200/client-login?token=test');
        self::assertSame([], $sender->recipients);
    }
}
