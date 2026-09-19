<?php

declare(strict_types=1);

namespace Tests\Shared\Translation;

use App\Shared\Translation\SharedJsonFileLoader;
use PHPUnit\Framework\TestCase;

final class SharedJsonFileLoaderTest extends TestCase
{
    public function testLoadsNestedKeysAndConvertsAngularPlaceholdersForSymfony(): void
    {
        $catalogue = (new SharedJsonFileLoader())->load(
            dirname(__DIR__, 4) . '/frontend/public/i18n/de.json',
            'de',
        );

        self::assertSame('Mässpäggli', $catalogue->get('app.title'));
        self::assertSame(
            'Mit deiner Unterstützung ermöglichen wir Familien mit knappem Budget einen Besuch der Messe %year%.',
            $catalogue->get('app.home.lead'),
        );
        self::assertSame(
            'Deine Mässpäggli-Bestellung ist definitiv bestätigt',
            $catalogue->get('app.mail.order.confirmation.definitive.subject'),
        );
    }
}
