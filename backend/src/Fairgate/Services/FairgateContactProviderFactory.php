<?php

declare(strict_types=1);

namespace App\Fairgate\Services;

use LogicException;

final class FairgateContactProviderFactory
{
    public static function create(): FairgateContactProvider
    {
        return match (getenv('APP_ENV')) {
            'test' => new FakeFairgateClient(),
            'prod' => new FairgateClient(),
            default => throw new LogicException('APP_ENV must be set to either test or prod.'),
        };
    }
}
