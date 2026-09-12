<?php

declare(strict_types=1);

namespace Tests\Auth;

use App\Auth\Services\PasswordPolicy;
use PHPUnit\Framework\TestCase;

final class PasswordPolicyTest extends TestCase
{
    public function testAcceptsPasswordsBetweenTwelveAnd128Characters(): void
    {
        self::assertTrue(PasswordPolicy::accepts(str_repeat('a', 12)));
        self::assertTrue(PasswordPolicy::accepts(str_repeat('a', 128)));
    }

    public function testRejectsPasswordsOutsideTheLengthRange(): void
    {
        self::assertFalse(PasswordPolicy::accepts(str_repeat('a', 11)));
        self::assertFalse(PasswordPolicy::accepts(str_repeat('a', 129)));
    }
}
