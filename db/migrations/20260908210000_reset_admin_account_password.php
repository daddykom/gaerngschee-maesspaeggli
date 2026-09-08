<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Migration\IrreversibleMigrationException;

final class ResetAdminAccountPassword extends AbstractMigration
{
    private const ADMIN_EMAIL = 'admin@gaerngschee.ch';
    private const PASSWORD_HASH = '$2y$12$EXjOJ.51uXs1DyB8SR2sUO6SMCnh6RuwkR2M8XDMJhlHhKP.UCZ12';

    public function up(): void
    {
        $this->execute(sprintf(
            "UPDATE users SET password = '%s' WHERE email = '%s'",
            self::PASSWORD_HASH,
            self::ADMIN_EMAIL,
        ));
    }

    public function down(): void
    {
        throw new IrreversibleMigrationException('The admin password reset cannot be reverted safely.');
    }
}
