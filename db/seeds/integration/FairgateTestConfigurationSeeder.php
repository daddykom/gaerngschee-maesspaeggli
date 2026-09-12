<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class FairgateTestConfigurationSeeder extends AbstractSeed
{
    public function run(): void
    {
        $configs = [
            ['00000000-0000-4000-8000-000000000015', 'campaign_year', '2026', 'Jahr der aktuellen Mässpäggli-Aktion und Bestellungen.', ['admin', 'client'], ['admin'], 'Aktionsjahr'],
            ['00000000-0000-4000-8000-000000000016', 'donation_url', 'https://aktionen.gaerngschee.ch/maesspaeggli/spenden', 'Link zur Spenden-Seite der Mässpäggli-Aktion.', ['admin', 'client'], ['admin'], 'Spenden-Link'],
            ['00000000-0000-4000-8000-000000000017', 'campaign_start_date', '2026-01-01', 'Startdatum der Mässpäggli-Aktion.', ['admin', 'client'], ['admin'], 'Aktionsstart'],
            ['00000000-0000-4000-8000-000000000018', 'campaign_end_date', '2026-12-31', 'Enddatum der Mässpäggli-Aktion.', ['admin', 'client'], ['admin'], 'Aktionsende'],
            ['00000000-0000-4000-8000-000000000010', 'fairgate_test_email', 'isabelle.joss@gaerngschee.ch', 'E-Mail-Adresse für den Fairgate-Verbindungstest.', ['admin'], ['admin'], 'Fairgate Test-E-Mail-Adresse'],
            ['00000000-0000-4000-8000-000000000011', 'fairgate_url', 'https://mein.fairgate.ch/vgbh/register/MTI0MTA=', 'Link zur Registrierung bei Fairgate.', ['client'], ['admin'], 'Fairgate-Registrierungslink'],
            ['00000000-0000-4000-8000-000000000012', 'fairgate_email_interval_days', '7', 'Abstand zwischen Erinnerungs-E-Mails in Tagen.', ['admin'], ['admin'], 'Fairgate-E-Mail-Abstand'],
            ['00000000-0000-4000-8000-000000000013', 'registration_token_retention_days', '365', 'Aufbewahrungsdauer abgelaufener Registrierungstokens in Tagen.', [], [], 'Registrierungstoken-Aufbewahrung'],
            ['00000000-0000-4000-8000-000000000014', 'provisional_order_recent_days', '14', 'Zeitraum für aktuelle provisorische Bestellungen in Tagen.', ['admin', 'user'], ['admin'], 'Zeitraum provisorischer Bestellungen'],
        ];

        foreach ($configs as [$id, $variableName, $value, $description, $accessGroup, $updateGroup, $label]) {
            if ($this->query('SELECT id FROM frontend_config WHERE variable_name = :variable_name', ['variable_name' => $variableName])->fetch() !== false) {
                continue;
            }

            $this->query(
                'INSERT INTO frontend_config (id, variable_name, value, description, access_group, update_group, label) VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label)',
                [
                    'id' => $id,
                    'variable_name' => $variableName,
                    'value' => json_encode($value, JSON_THROW_ON_ERROR),
                    'description' => $description,
                    'access_group' => json_encode($accessGroup, JSON_THROW_ON_ERROR),
                    'update_group' => json_encode($updateGroup, JSON_THROW_ON_ERROR),
                    'label' => $label,
                ],
            );
        }
    }
}
