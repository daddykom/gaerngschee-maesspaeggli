<?php

declare(strict_types=1);

namespace Tests\Fairgate;

use App\Fairgate\Services\FakeFairgateClient;
use App\Fairgate\Services\FairgateClient;
use App\Fairgate\Services\FairgateContactProviderFactory;
use LogicException;
use PHPUnit\Framework\TestCase;

final class FairgateContactProviderFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('APP_ENV');
    }

    public function testTestEnvironmentUsesFakeClient(): void
    {
        putenv('APP_ENV=test');

        self::assertInstanceOf(FakeFairgateClient::class, FairgateContactProviderFactory::create());
    }

    public function testProductionEnvironmentUsesRealClient(): void
    {
        putenv('APP_ENV=prod');

        self::assertInstanceOf(FairgateClient::class, FairgateContactProviderFactory::create());
    }

    public function testUnknownEnvironmentIsRejected(): void
    {
        putenv('APP_ENV=unknown');

        $this->expectException(LogicException::class);
        FairgateContactProviderFactory::create();
    }
}
