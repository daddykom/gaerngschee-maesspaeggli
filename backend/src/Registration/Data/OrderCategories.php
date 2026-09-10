<?php

declare(strict_types=1);

namespace App\Registration\Data;

final class OrderCategories
{
    /** @var list<string> */
    public const ADULT = ['catA', 'catB'];

    /** @var list<string> */
    public const CHILD = ['catC', 'catD', 'catE', 'catF', 'catG'];

    /** @var list<string> */
    public const ALL = [...self::ADULT, ...self::CHILD];

    public const ADULT_FALLBACK = 'catA';
    public const CHILD_FALLBACK = 'catC';
}
