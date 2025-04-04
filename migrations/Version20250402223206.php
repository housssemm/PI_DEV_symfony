<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402223206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update discriminator values for createur_evenement';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE user SET discr = \'createur_evenement\' WHERE id IN (SELECT user_id FROM createur_evenement)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE user SET discr = \'user\' WHERE id IN (SELECT user_id FROM createur_evenement)');
    }
}
