<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230121160807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE domaine (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, sigle VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formation (id INT AUTO_INCREMENT NOT NULL, type_diplome_id INT DEFAULT NULL, domaine_id INT DEFAULT NULL, mention_id INT DEFAULT NULL, responsable_mention_id INT DEFAULT NULL, mention_texte VARCHAR(255) DEFAULT NULL, niveau_entree VARCHAR(20) NOT NULL, niveau_sortie VARCHAR(20) NOT NULL, inscription_rncp TINYINT(1) NOT NULL, code_rncp VARCHAR(10) DEFAULT NULL, INDEX IDX_404021BF3BFB8FC7 (type_diplome_id), INDEX IDX_404021BF4272FC9F (domaine_id), INDEX IDX_404021BF7A4147F0 (mention_id), INDEX IDX_404021BF8E68B4F0 (responsable_mention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formation_site (formation_id INT NOT NULL, site_id INT NOT NULL, INDEX IDX_3E9CECA45200282E (formation_id), INDEX IDX_3E9CECA4F6BD1646 (site_id), PRIMARY KEY(formation_id, site_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mention (id INT AUTO_INCREMENT NOT NULL, type_diplome_id INT DEFAULT NULL, domaine_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, sigle VARCHAR(20) NOT NULL, INDEX IDX_E20259CD3BFB8FC7 (type_diplome_id), INDEX IDX_E20259CD4272FC9F (domaine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_diplome (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, sigle VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF3BFB8FC7 FOREIGN KEY (type_diplome_id) REFERENCES type_diplome (id)');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF4272FC9F FOREIGN KEY (domaine_id) REFERENCES domaine (id)');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF7A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF8E68B4F0 FOREIGN KEY (responsable_mention_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE formation_site ADD CONSTRAINT FK_3E9CECA45200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation_site ADD CONSTRAINT FK_3E9CECA4F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mention ADD CONSTRAINT FK_E20259CD3BFB8FC7 FOREIGN KEY (type_diplome_id) REFERENCES type_diplome (id)');
        $this->addSql('ALTER TABLE mention ADD CONSTRAINT FK_E20259CD4272FC9F FOREIGN KEY (domaine_id) REFERENCES domaine (id)');
        $this->addSql('ALTER TABLE user ADD nom VARCHAR(50) NOT NULL, ADD prenom VARCHAR(50) NOT NULL, ADD email VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF3BFB8FC7');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF4272FC9F');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF7A4147F0');
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF8E68B4F0');
        $this->addSql('ALTER TABLE formation_site DROP FOREIGN KEY FK_3E9CECA45200282E');
        $this->addSql('ALTER TABLE formation_site DROP FOREIGN KEY FK_3E9CECA4F6BD1646');
        $this->addSql('ALTER TABLE mention DROP FOREIGN KEY FK_E20259CD3BFB8FC7');
        $this->addSql('ALTER TABLE mention DROP FOREIGN KEY FK_E20259CD4272FC9F');
        $this->addSql('DROP TABLE domaine');
        $this->addSql('DROP TABLE formation');
        $this->addSql('DROP TABLE formation_site');
        $this->addSql('DROP TABLE mention');
        $this->addSql('DROP TABLE type_diplome');
        $this->addSql('ALTER TABLE `user` DROP nom, DROP prenom, DROP email');
    }
}
