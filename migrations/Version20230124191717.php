<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230124191717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation ADD has_stage TINYINT(1) NOT NULL, ADD stage_text LONGTEXT DEFAULT NULL, ADD nb_heures_stages DOUBLE PRECISION DEFAULT NULL, ADD has_projet TINYINT(1) NOT NULL, ADD projet_text LONGTEXT DEFAULT NULL, ADD nb_heures_projet DOUBLE PRECISION NOT NULL, ADD has_memoire TINYINT(1) NOT NULL, ADD memoire_text LONGTEXT DEFAULT NULL, ADD nb_heures_memoire DOUBLE PRECISION NOT NULL, ADD modalites_enseignement INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation DROP has_stage, DROP stage_text, DROP nb_heures_stages, DROP has_projet, DROP projet_text, DROP nb_heures_projet, DROP has_memoire, DROP memoire_text, DROP nb_heures_memoire, DROP modalites_enseignement');
    }
}
