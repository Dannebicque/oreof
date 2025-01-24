<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230125093807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE composante ADD responsable_dpe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE composante ADD CONSTRAINT FK_D8E84C8ED508662 FOREIGN KEY (responsable_dpe_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_D8E84C8ED508662 ON composante (responsable_dpe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE composante DROP FOREIGN KEY FK_D8E84C8ED508662');
        $this->addSql('DROP INDEX IDX_D8E84C8ED508662 ON composante');
        $this->addSql('ALTER TABLE composante DROP responsable_dpe_id');
    }
}
