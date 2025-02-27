<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230122160509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF3BFB8FC7');
        $this->addSql('ALTER TABLE mention DROP FOREIGN KEY FK_E20259CD3BFB8FC7');
        $this->addSql('DROP TABLE type_diplome');
        $this->addSql('DROP INDEX IDX_404021BF3BFB8FC7 ON formation');
        $this->addSql('ALTER TABLE formation ADD type_diplome VARCHAR(255) DEFAULT NULL, DROP type_diplome_id');
        $this->addSql('DROP INDEX IDX_E20259CD3BFB8FC7 ON mention');
        $this->addSql('ALTER TABLE mention ADD type_diplome VARCHAR(255) NOT NULL, DROP type_diplome_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE type_diplome (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, sigle VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE formation ADD type_diplome_id INT DEFAULT NULL, DROP type_diplome');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF3BFB8FC7 FOREIGN KEY (type_diplome_id) REFERENCES type_diplome (id)');
        $this->addSql('CREATE INDEX IDX_404021BF3BFB8FC7 ON formation (type_diplome_id)');
        $this->addSql('ALTER TABLE mention ADD type_diplome_id INT DEFAULT NULL, DROP type_diplome');
        $this->addSql('ALTER TABLE mention ADD CONSTRAINT FK_E20259CD3BFB8FC7 FOREIGN KEY (type_diplome_id) REFERENCES type_diplome (id)');
        $this->addSql('CREATE INDEX IDX_E20259CD3BFB8FC7 ON mention (type_diplome_id)');
    }
}
