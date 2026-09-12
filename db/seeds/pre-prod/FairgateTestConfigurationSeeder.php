<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class FairgateTestConfigurationSeeder extends AbstractSeed
{
    public function run(): void
    {
        $fieldMetadata = [
            'campaign_year' => ['pattern' => '\\d{4}', 'placeholder' => 'z. B. 2026'],
            'donation_url' => ['pattern' => 'https://[^\\s]+', 'placeholder' => 'https://...'],
            'campaign_start_date' => ['pattern' => '\\d{4}-\\d{2}-\\d{2}', 'placeholder' => 'JJJJ-MM-TT'],
            'campaign_end_date' => ['pattern' => '\\d{4}-\\d{2}-\\d{2}', 'placeholder' => 'JJJJ-MM-TT'],
            'startDate' => ['pattern' => '\\d{4}-\\d{2}-\\d{2}', 'placeholder' => 'JJJJ-MM-TT'],
            'endDate' => ['pattern' => '\\d{4}-\\d{2}-\\d{2}', 'placeholder' => 'JJJJ-MM-TT'],
            'closeDate' => ['pattern' => '\\d{4}-\\d{2}-\\d{2}', 'placeholder' => 'JJJJ-MM-TT'],
            'fairgate_test_email' => ['pattern' => '[^@\\s]+@[^@\\s]+\\.[^@\\s]+', 'placeholder' => 'name@beispiel.ch'],
            'registration_token_retention_days' => ['pattern' => '[1-9]\\d*', 'placeholder' => 'z. B. 365'],
            'fairgate_email_interval_days' => ['pattern' => '[1-9]\\d*', 'placeholder' => 'z. B. 7'],
            'fairgate_url' => ['pattern' => 'https://[^\\s]+', 'placeholder' => 'https://...'],
            'provisional_order_recent_days' => ['pattern' => '[1-9]\\d*', 'placeholder' => 'z. B. 14'],
        ];

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
                'value' => '2026-01-01',
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
        ] as $config) {
            if ($this->query('SELECT id FROM frontend_config WHERE variable_name = :variable_name', ['variable_name' => $config['variable_name']])->fetch() === false) {
                $this->query(
                    'INSERT INTO frontend_config (id, variable_name, value, description, access_group, update_group, label)
                     VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label)',
                    [
                        'id' => $config['id'],
                        'variable_name' => $config['variable_name'],
                        'value' => json_encode($config['value'], JSON_THROW_ON_ERROR),
                        'description' => $config['description'],
                        'access_group' => json_encode($config['access_group'], JSON_THROW_ON_ERROR),
                        'update_group' => json_encode($config['update_group'], JSON_THROW_ON_ERROR),
                        'label' => $config['label'],
                    ],
                );
            }
        }

        $variableName = 'fairgate_test_email';
        if ($this->query(
            'SELECT id FROM frontend_config WHERE variable_name = :variable_name',
            ['variable_name' => $variableName],
        )->fetch() === false) {
            $this->query(
                'INSERT INTO frontend_config
                    (id, variable_name, value, description, access_group, update_group, label)
                 VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label)',
                [
                    'id' => '00000000-0000-4000-8000-000000000010',
                    'variable_name' => $variableName,
                    'value' => json_encode('isabelle.joss@gaerngschee.ch', JSON_THROW_ON_ERROR),
                    'description' => 'E-Mail-Adresse für den Fairgate-Verbindungstest.',
                    'access_group' => json_encode(['admin'], JSON_THROW_ON_ERROR),
                    'update_group' => json_encode(['admin'], JSON_THROW_ON_ERROR),
                    'label' => 'Fairgate Test-E-Mail-Adresse',
                ],
            );
        }

        $variableName = 'fairgate_email_interval_days';
        if ($this->query('SELECT id FROM frontend_config WHERE variable_name = :variable_name', ['variable_name' => $variableName])->fetch() === false) {
            $this->query(
                'INSERT INTO frontend_config (id, variable_name, value, description, access_group, update_group, label)
                 VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label)',
                [
                    'id' => '00000000-0000-4000-8000-000000000012',
                    'variable_name' => $variableName,
                    'value' => json_encode('7', JSON_THROW_ON_ERROR),
                    'description' => 'Abstand zwischen Erinnerungs-E-Mails in Tagen.',
                    'access_group' => json_encode(['admin'], JSON_THROW_ON_ERROR),
                    'update_group' => json_encode(['admin'], JSON_THROW_ON_ERROR),
                    'label' => 'Fairgate-E-Mail-Abstand',
                ],
            );
        }

        $variableName = 'registration_token_retention_days';
        if ($this->query('SELECT id FROM frontend_config WHERE variable_name = :variable_name', ['variable_name' => $variableName])->fetch() === false) {
            $this->query(
                'INSERT INTO frontend_config (id, variable_name, value, description, access_group, update_group, label)
                 VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label)',
                [
                    'id' => '00000000-0000-4000-8000-000000000013',
                    'variable_name' => $variableName,
                    'value' => json_encode('365', JSON_THROW_ON_ERROR),
                    'description' => 'Aufbewahrungsdauer abgelaufener Registrierungstokens in Tagen.',
                    'access_group' => json_encode([], JSON_THROW_ON_ERROR),
                    'update_group' => json_encode([], JSON_THROW_ON_ERROR),
                    'label' => 'Registrierungstoken-Aufbewahrung',
                ],
            );
        }

        $variableName = 'fairgate_url';
        if ($this->query(
            'SELECT id FROM frontend_config WHERE variable_name = :variable_name',
            ['variable_name' => $variableName],
        )->fetch() === false) {
            $this->query(
                'INSERT INTO frontend_config
                    (id, variable_name, value, description, access_group, update_group, label)
                 VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label)',
                [
                    'id' => '00000000-0000-4000-8000-000000000011',
                    'variable_name' => $variableName,
                    'value' => json_encode('https://mein.fairgate.ch/vgbh/register/MTI0MTA=', JSON_THROW_ON_ERROR),
                    'description' => 'Link zur Registrierung bei Fairgate.',
                    'access_group' => json_encode(['client'], JSON_THROW_ON_ERROR),
                    'update_group' => json_encode(['admin'], JSON_THROW_ON_ERROR),
                    'label' => 'Fairgate-Registrierungslink',
                ],
            );
        }

        $variableName = 'provisional_order_recent_days';
        if ($this->query(
            'SELECT id FROM frontend_config WHERE variable_name = :variable_name',
            ['variable_name' => $variableName],
        )->fetch() === false) {
            $this->query(
                'INSERT INTO frontend_config
                    (id, variable_name, value, description, access_group, update_group, label)
                 VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label)',
                [
                    'id' => '00000000-0000-4000-8000-000000000014',
                    'variable_name' => $variableName,
                    'value' => json_encode('14', JSON_THROW_ON_ERROR),
                    'description' => 'Zeitraum für aktuelle provisorische Bestellungen in Tagen.',
                    'access_group' => json_encode(['admin', 'user'], JSON_THROW_ON_ERROR),
                    'update_group' => json_encode(['admin'], JSON_THROW_ON_ERROR),
                    'label' => 'Zeitraum provisorischer Bestellungen',
                ],
            );
        }

        foreach ($fieldMetadata as $variableName => $metadata) {
            $this->query(
                'UPDATE frontend_config SET pattern = :pattern, placeholder = :placeholder WHERE variable_name = :variable_name',
                ['pattern' => $metadata['pattern'], 'placeholder' => $metadata['placeholder'], 'variable_name' => $variableName],
            );
        }
    }
}
