<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701203311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'program and comment translation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_comment_machine_translation (id INT AUTO_INCREMENT NOT NULL, comment_id INT DEFAULT NULL, source_language VARCHAR(5) NOT NULL, target_language VARCHAR(5) NOT NULL, provider VARCHAR(255) NOT NULL, usage_count INT NOT NULL, last_modified_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_A36970C8F8697D13 (comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_machine_translation (id INT AUTO_INCREMENT NOT NULL, project_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', source_language VARCHAR(5) NOT NULL, target_language VARCHAR(5) NOT NULL, provider VARCHAR(255) NOT NULL, usage_count INT NOT NULL, last_modified_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_2FCF7039166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_comment_machine_translation ADD CONSTRAINT FK_A36970C8F8697D13 FOREIGN KEY (comment_id) REFERENCES user_comment (id)');
        $this->addSql('ALTER TABLE project_machine_translation ADD CONSTRAINT FK_2FCF7039166D1F9C FOREIGN KEY (project_id) REFERENCES program (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_comment_machine_translation');
        $this->addSql('DROP TABLE project_machine_translation');
    }
}
