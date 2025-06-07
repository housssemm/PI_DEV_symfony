<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240320000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create migrations table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS doctrine_migrations (
            version VARCHAR(191) NOT NULL,
            executed_at DATETIME DEFAULT NULL,
            execution_time INT DEFAULT NULL,
            PRIMARY KEY(version)
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS doctrine_migrations');
    }
}
