<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230119075450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE annee_universitaire (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(30) NOT NULL, annee INT NOT NULL, defaut TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE composante (id INT AUTO_INCREMENT NOT NULL, directeur_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, INDEX IDX_D8E84C8E82E7EE8 (directeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dpe (id INT AUTO_INCREMENT NOT NULL, current_place VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etablissement (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, etablissement_id INT DEFAULT NULL, libelle VARCHAR(255) NOT NULL, INDEX IDX_694309E4FF631228 (etablissement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, etablissement_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), INDEX IDX_8D93D649FF631228 (etablissement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE composante ADD CONSTRAINT FK_D8E84C8E82E7EE8 FOREIGN KEY (directeur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE composante DROP FOREIGN KEY FK_D8E84C8E82E7EE8');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4FF631228');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649FF631228');
        $this->addSql('DROP TABLE annee_universitaire');
        $this->addSql('DROP TABLE composante');
        $this->addSql('DROP TABLE dpe');
        $this->addSql('DROP TABLE etablissement');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE `user`');
    }
}
