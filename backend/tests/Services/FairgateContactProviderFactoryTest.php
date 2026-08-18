<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\FakeFairgateClient;
use App\Services\FairgateClient;
use App\Services\FairgateContactProviderFactory;
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
