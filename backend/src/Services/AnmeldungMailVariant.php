<?php

declare(strict_types=1);

namespace App\Services;

enum AnmeldungMailVariant: string
{
    case UserAndFairgate = 'user-and-fairgate';
    case UserMissingFairgate = 'user-missing-fairgate';
    case UserMissingFairgateMissing = 'user-missing-fairgate-missing';
    case UserAndFairgateMissing = 'user-and-fairgate-missing';

    public static function fromChecks(bool $userExists, bool $fairgateExists): self
    {
        return match ([$userExists, $fairgateExists]) {
            [true, true] => self::UserAndFairgate,
            [false, true] => self::UserMissingFairgate,
            [false, false] => self::UserMissingFairgateMissing,
            [true, false] => self::UserAndFairgateMissing,
        };
    }
}
