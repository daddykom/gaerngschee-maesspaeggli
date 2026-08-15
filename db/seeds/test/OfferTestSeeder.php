<?php
declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class OfferTestSeeder extends AbstractSeed
{
    public function run(): void
    {
        $offers = [
            [
                'id' => '1',
                'title' => 'Kleidung tauschen',
                'description' => 'Tausche deine alte Kleidung gegen neue Stücke.',
                'category' => 'kleidung',
                'latitude' => 47.5596,
                'longitude' => 7.5886,
                'address' => 'Musterstraße 1, 4051 Basel',
                'status' => 'published',
                'contact_name' => 'Maria Müller',
                'contact_email' => null,
                'contact_phone' => null,
                'image_url' => null,
            ],
            [
                'id' => '2',
                'title' => 'Büchertausch',
                'description' => 'Gemeinsames Tauschen von Büchern.',
                'category' => 'buecher',
                'latitude' => 47.5575,
                'longitude' => 7.592,
                'address' => 'Lesecafé, Hauptstraße 5, 4051 Basel',
                'status' => 'published',
                'contact_name' => 'Thomas Schmidt',
                'contact_email' => 'thomas@buecherfreunde.de',
                'contact_phone' => null,
                'image_url' => null,
            ],
            [
                'id' => '3',
                'title' => 'Spieleabend',
                'description' => 'Kostenloser Spieleabend für alle.',
                'category' => 'aktivitaeten',
                'latitude' => 47.561,
                'longitude' => 7.585,
                'address' => 'Jugendzentrum, Nebenstraße 3, 4051 Basel',
                'status' => 'published',
                'contact_name' => 'Jugendzentrum Team',
                'contact_email' => null,
                'contact_phone' => '+41 61 123 45 67',
                'image_url' => null,
            ],
        ];

        $this->table('offers')->insert($offers)->save();
    }
}