<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class AdminAccountSeeder extends AbstractSeed
{
    public function run(): void
    {
        $email = 'admin@gaerngschee.ch';
        if ($this->query('SELECT id FROM users WHERE email = :email', ['email' => $email])->fetch() !== false) {
            return;
        }

        $this->query(
            'INSERT INTO users (id, email, password, `group`, required_password_reset) VALUES (:id, :email, :password, :group, :required_password_reset)',
            [
                'id' => '00000000-0000-4000-8000-000000000001',
                'email' => $email,
                'password' => password_hash(getenv('ADMIN_SEED_PASSWORD') ?: 'secret', PASSWORD_DEFAULT),
                'group' => 'admin',
                'required_password_reset' => 0,
            ],
        );

        $this->query(
            'INSERT INTO users (id, email, password, `group`, required_password_reset) VALUES (:id, :email, :password, :group, :required_password_reset)',
            [
                'id' => '00000000-0000-4000-8000-000000000002',
                'email' => 'user@gaerngschee.ch',
                'password' => password_hash('secret', PASSWORD_DEFAULT),
                'group' => 'user',
                'required_password_reset' => 0,
            ],
        );
    }
}
