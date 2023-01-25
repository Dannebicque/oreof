<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230124202346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bloc_competence (id INT AUTO_INCREMENT NOT NULL, formation_id INT DEFAULT NULL, code VARCHAR(10) NOT NULL, libelle VARCHAR(255) NOT NULL, INDEX IDX_711471E95200282E (formation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competence (id INT AUTO_INCREMENT NOT NULL, bloc_competence_id INT DEFAULT NULL, code VARCHAR(10) NOT NULL, libelle VARCHAR(255) NOT NULL, INDEX IDX_94D4687FEC8CB2A4 (bloc_competence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bloc_competence ADD CONSTRAINT FK_711471E95200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE competence ADD CONSTRAINT FK_94D4687FEC8CB2A4 FOREIGN KEY (bloc_competence_id) REFERENCES bloc_competence (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bloc_competence DROP FOREIGN KEY FK_711471E95200282E');
        $this->addSql('ALTER TABLE competence DROP FOREIGN KEY FK_94D4687FEC8CB2A4');
        $this->addSql('DROP TABLE bloc_competence');
        $this->addSql('DROP TABLE competence');
    }
}
