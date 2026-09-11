<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class FairgateTestConfigurationSeeder extends AbstractSeed
{
    public function run(): void
    {
        foreach ([
            [
                'id' => '00000000-0000-4000-8000-000000000015',
                'variable_name' => 'campaign_year',
                'value' => '2026',
                'description' => 'Jahr der aktuellen Mässpäggli-Aktion und Bestellungen.',
                'access_group' => ['admin', 'client'],
                'update_group' => ['admin'],
                'label' => 'Aktionsjahr',
            ],
            [
                'id' => '00000000-0000-4000-8000-000000000016',
                'variable_name' => 'donation_url',
                'value' => 'https://aktionen.gaerngschee.ch/maesspaeggli/spenden',
                'description' => 'Link zur Spenden-Seite der Mässpäggli-Aktion.',
                'access_group' => ['admin', 'client'],
                'update_group' => ['admin'],
                'label' => 'Spenden-Link',
            ],
            [
                'id' => '00000000-0000-4000-8000-000000000017',
                'variable_name' => 'campaign_start_date',
                'value' => '2026-10-01',
                'description' => 'Startdatum der Mässpäggli-Aktion.',
                'access_group' => ['admin', 'client'],
                'update_group' => ['admin'],
                'label' => 'Aktionsstart',
            ],
            [
                'id' => '00000000-0000-4000-8000-000000000018',
                'variable_name' => 'campaign_end_date',
                'value' => '2026-12-31',
                'description' => 'Enddatum der Mässpäggli-Aktion.',
                'access_group' => ['admin', 'client'],
                'update_group' => ['admin'],
                'label' => 'Aktionsende',
            ],
            [
                'id' => '00000000-0000-4000-8000-000000000010',
                'variable_name' => 'fairgate_test_email',
                'value' => 'isabelle.joss@gaerngschee.ch',
                'description' => 'E-Mail-Adresse für den Fairgate-Verbindungstest.',
                'access_group' => ['admin'],
                'update_group' => [],
                'label' => 'Fairgate Test-E-Mail-Adresse',
            ],
            [
                'id' => '00000000-0000-4000-8000-000000000011',
                'variable_name' => 'fairgate_url',
                'value' => 'https://mein.fairgate.ch/vgbh/register/MTI0MTA=',
                'description' => 'Link zur Registrierung bei Fairgate.',
                'access_group' => ['admin'],
                'update_group' => ['admin'],
                'label' => 'Fairgate-Registrierungslink',
            ],
            [
                'id' => '00000000-0000-4000-8000-000000000012',
                'variable_name' => 'fairgate_email_interval_days',
                'value' => '7',
                'description' => 'Abstand zwischen Erinnerungs-E-Mails in Tagen.',
                'access_group' => ['admin'],
                'update_group' => ['admin'],
                'label' => 'Fairgate-E-Mail-Abstand',
            ],
            [
                'id' => '00000000-0000-4000-8000-000000000013',
                'variable_name' => 'registration_token_retention_days',
                'value' => '365',
                'description' => 'Aufbewahrungsdauer abgelaufener Registrierungstokens in Tagen.',
                'access_group' => [],
                'update_group' => [],
                'label' => 'Registrierungstoken-Aufbewahrung',
            ],
            [
                'id' => '00000000-0000-4000-8000-000000000014',
                'variable_name' => 'provisional_order_recent_days',
                'value' => '14',
                'description' => 'Zeitraum für aktuelle provisorische Bestellungen in Tagen.',
                'access_group' => ['admin', 'user'],
                'update_group' => ['admin'],
                'label' => 'Zeitraum provisorischer Bestellungen',
            ],
        ] as $config) {
            $encodedValue = json_encode($config['value'], JSON_THROW_ON_ERROR);
            $encodedAccessGroup = json_encode($config['access_group'], JSON_THROW_ON_ERROR);
            $encodedUpdateGroup = json_encode($config['update_group'], JSON_THROW_ON_ERROR);
            $existing = $this->query(
                'SELECT id FROM frontend_config WHERE variable_name = :variable_name',
                ['variable_name' => $config['variable_name']],
            )->fetch();

            if ($existing !== false) {
                $this->query(
                    'UPDATE frontend_config
                     SET value = :value, description = :description, access_group = :access_group,
                         update_group = :update_group, label = :label
                     WHERE variable_name = :variable_name',
                    [
                        'value' => $encodedValue,
                        'description' => $config['description'],
                        'access_group' => $encodedAccessGroup,
                        'update_group' => $encodedUpdateGroup,
                        'label' => $config['label'],
                        'variable_name' => $config['variable_name'],
                    ],
                );
                continue;
            }

            $this->query(
                'INSERT INTO frontend_config
                    (id, variable_name, value, description, access_group, update_group, label)
                 VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label)',
                [
                    'id' => $config['id'],
                    'variable_name' => $config['variable_name'],
                    'value' => $encodedValue,
                    'description' => $config['description'],
                    'access_group' => $encodedAccessGroup,
                    'update_group' => $encodedUpdateGroup,
                    'label' => $config['label'],
                ],
            );
        }
    }
}
