<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240320000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update progress_post table to make image columns nullable';
    }

    public function up(Schema $schema): void
    {
        // First, drop the existing table if it exists
        $this->addSql('DROP TABLE IF EXISTS progress_post');

        // Create the table with nullable image columns
        $this->addSql('CREATE TABLE progress_post (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            current_weight DOUBLE PRECISION NOT NULL,
            goal_weight DOUBLE PRECISION NOT NULL,
            before_image VARCHAR(255) DEFAULT NULL,
            after_image VARCHAR(255) DEFAULT NULL,
            is_public TINYINT(1) NOT NULL,
            likes INT NOT NULL,
            comments INT NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_PROGRESS_POST_USER (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraint
        $this->addSql('ALTER TABLE progress_post ADD CONSTRAINT FK_PROGRESS_POST_USER FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS progress_post');
    }
}
