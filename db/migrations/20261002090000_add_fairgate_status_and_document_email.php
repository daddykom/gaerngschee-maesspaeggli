<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFairgateStatusAndDocumentEmail extends AbstractMigration
{
    public function up(): void
    {
        $this->table('orders')
            ->addColumn('fairgate_status', 'string', ['limit' => 20, 'null' => true, 'default' => null])
            ->update();

        if ($this->fetchRow("SELECT id FROM frontend_config WHERE variable_name = 'fairgate_document_email'") !== false) {
            return;
        }

        $this->execute(
            'INSERT INTO frontend_config
                (id, variable_name, value, description, access_group, update_group, label, pattern, placeholder, sort_order)
             VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label, :pattern, :placeholder, :sort_order)',
            [
                'id' => '00000000-0000-4000-8000-000000000019',
                'variable_name' => 'fairgate_document_email',
                'value' => json_encode('Info@gaerngschee.ch', JSON_THROW_ON_ERROR),
                'description' => 'E-Mail-Adresse für das Nachreichen eines gültigen Bedürftigkeitsausweises.',
                'access_group' => json_encode(['admin', 'client'], JSON_THROW_ON_ERROR),
                'update_group' => json_encode(['admin'], JSON_THROW_ON_ERROR),
                'label' => 'E-Mail-Adresse für Bedürftigkeitsausweise',
                'pattern' => '[^@\\s]+@[^@\\s]+\\.[^@\\s]+',
                'placeholder' => 'name@beispiel.ch',
                'sort_order' => 85,
            ],
        );
    }

    public function down(): void
    {
        $this->execute("DELETE FROM frontend_config WHERE variable_name = 'fairgate_document_email'");
        $this->table('orders')->removeColumn('fairgate_status')->update();
    }
}
