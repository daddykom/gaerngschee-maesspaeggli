<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class AdminAccountSeeder extends AbstractSeed
{
    public function run(): void
    {
        $email = 'admin@gaerngschee.ch';
        $existingUser = $this->query(
            'SELECT id FROM users WHERE email = :email',
            ['email' => $email],
        )->fetch();

        if ($existingUser !== false) {
            return;
        }

        $this->query(
            'INSERT INTO users (id, email, password, `group`, required_password_reset) VALUES (:id, :email, :password, :group, :required_password_reset)',
            [
                'id' => '00000000-0000-4000-8000-000000000001',
                'email' => $email,
                'password' => $this->adminPasswordHash(),
                'group' => 'admin',
                'required_password_reset' => 1,
            ],
        );
    }

    private function adminPasswordHash(): string
    {
        $password = getenv('ADMIN_SEED_PASSWORD');
        if (is_string($password) && $password !== '') {
            return password_hash($password, PASSWORD_DEFAULT);
        }

        return password_hash('secret', PASSWORD_DEFAULT) ?: throw new RuntimeException('Could not hash the test admin password.');
    }

}
