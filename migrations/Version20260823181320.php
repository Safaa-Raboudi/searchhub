<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260823181320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the documents table (id, title, content, type, tags, status, created_at, updated_at) with indexes on status and type.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE documents (id UUID NOT NULL, title VARCHAR(255) NOT NULL, content TEXT NOT NULL, type VARCHAR(255) NOT NULL, tags JSON NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_documents_status ON documents (status)');
        $this->addSql('CREATE INDEX idx_documents_type ON documents (type)');
        $this->addSql('COMMENT ON COLUMN documents.id IS \'(DC2Type:document_id)\'');
        $this->addSql('COMMENT ON COLUMN documents.status IS \'(DC2Type:document_status)\'');
        $this->addSql('COMMENT ON COLUMN documents.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN documents.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE documents');
    }
}
