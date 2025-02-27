<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230124133109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD is_enable TINYINT(1) NOT NULL, ADD is_valid_dpe TINYINT(1) NOT NULL, ADD date_valide_dpe DATETIME DEFAULT NULL, ADD is_valide_administration TINYINT(1) NOT NULL, ADD date_valide_administration DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP is_enable, DROP is_valid_dpe, DROP date_valide_dpe, DROP is_valide_administration, DROP date_valide_administration');
    }
}
