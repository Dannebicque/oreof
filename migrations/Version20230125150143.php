<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230125150143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE formation_composante');
        $this->addSql('CREATE TABLE formation_composante (formation_id INT NOT NULL, composante_id INT NOT NULL, INDEX IDX_F52657F15200282E (formation_id), INDEX IDX_F52657F1AC12F1AD (composante_id), PRIMARY KEY(formation_id, composante_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE formation_composante ADD CONSTRAINT FK_F52657F15200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation_composante ADD CONSTRAINT FK_F52657F1AC12F1AD FOREIGN KEY (composante_id) REFERENCES composante (id) ON DELETE CASCADE');
        // $this->addSql('ALTER TABLE bloc_competence DROP FOREIGN KEY FK_711471E95200282E');
        // $this->addSql('DROP INDEX IDX_711471E95200282E ON bloc_competence');
        $this->addSql('ALTER TABLE bloc_competence DROP formation_id');
        $this->addSql('ALTER TABLE formation ADD annee_universitaire_id INT DEFAULT NULL, ADD niveau_entree INT NOT NULL, ADD ih_rncp TINYINT(1) NOT NULL, ADD regime_inscription VARCHAR(20) DEFAULT NULL, ADD regime_inscription_texte LONGTEXT DEFAULT NULL, ADD modalites_alternance LONGTEXT DEFAULT NULL, ADD etat_dpe VARCHAR(50) NOT NULL, ADD created DATETIME NOT NULL, ADD updated DATETIME NOT NULL, DROP niveau_entree, DROP inscription_rncp, DROP has_parcours, DROP contenu_formation, DROP resultats_attendus, DROP rythme_formation_texte, DROP has_stage, DROP stage_text, DROP nb_heures_stages, DROP has_projet, DROP projet_text, DROP nb_heures_projet, DROP has_memoire, DROP memoire_text, DROP nb_heures_memoire, DROP rythme_formation, DROP prerequis, CHANGE niveau_sortie niveau_sortie INT NOT NULL, CHANGE code_rncp code_rncp VARCHAR(20) DEFAULT NULL, CHANGE type_diplome type_diplome VARCHAR(255) NOT NULL, CHANGE modalites_enseignement composante_porteuse_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF2C43320F FOREIGN KEY (composante_porteuse_id) REFERENCES composante (id)');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF544BFD58 FOREIGN KEY (annee_universitaire_id) REFERENCES annee_universitaire (id)');
        $this->addSql('CREATE INDEX IDX_404021BF2C43320F ON formation (composante_porteuse_id)');
        $this->addSql('CREATE INDEX IDX_404021BF544BFD58 ON formation (annee_universitaire_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation_composante DROP FOREIGN KEY FK_F52657F15200282E');
        $this->addSql('ALTER TABLE formation_composante DROP FOREIGN KEY FK_F52657F1AC12F1AD');
        $this->addSql('DROP TABLE formation_composante');
        $this->addSql('ALTER TABLE bloc_competence ADD formation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bloc_competence ADD CONSTRAINT FK_711471E95200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('CREATE INDEX IDX_711471E95200282E ON bloc_competence (formation_id)');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF2C43320F');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF544BFD58');
        $this->addSql('DROP INDEX IDX_404021BF2C43320F ON formation');
        $this->addSql('DROP INDEX IDX_404021BF544BFD58 ON formation');
        $this->addSql('ALTER TABLE formation ADD niveau_entree VARCHAR(20) NOT NULL, ADD has_parcours TINYINT(1) NOT NULL, ADD contenu_formation LONGTEXT DEFAULT NULL, ADD resultats_attendus LONGTEXT DEFAULT NULL, ADD rythme_formation_texte LONGTEXT DEFAULT NULL, ADD has_stage TINYINT(1) NOT NULL, ADD stage_text LONGTEXT DEFAULT NULL, ADD nb_heures_stages DOUBLE PRECISION DEFAULT NULL, ADD has_projet TINYINT(1) NOT NULL, ADD projet_text LONGTEXT DEFAULT NULL, ADD nb_heures_projet DOUBLE PRECISION NOT NULL, ADD has_memoire TINYINT(1) NOT NULL, ADD memoire_text LONGTEXT DEFAULT NULL, ADD nb_heures_memoire DOUBLE PRECISION NOT NULL, ADD modalites_enseignement INT DEFAULT NULL, ADD rythme_formation VARCHAR(30) DEFAULT NULL, ADD prerequis LONGTEXT DEFAULT NULL, DROP composante_porteuse_id, DROP annee_universitaire_id, DROP niveau_entr�ee, DROP regime_inscription, DROP regime_inscription_texte, DROP modalites_alternance, DROP etat_dpe, DROP created, DROP updated, CHANGE type_diplome type_diplome VARCHAR(255) DEFAULT NULL, CHANGE niveau_sortie niveau_sortie VARCHAR(20) NOT NULL, CHANGE code_rncp code_rncp VARCHAR(10) DEFAULT NULL, CHANGE ih_rncp inscription_rncp TINYINT(1) NOT NULL');
    }
}
