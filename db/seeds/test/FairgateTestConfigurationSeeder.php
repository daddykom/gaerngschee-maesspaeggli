<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class FairgateTestConfigurationSeeder extends AbstractSeed
{
    public function run(): void
    {
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
    }
}
