<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027082720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parcours_versioning (id INT AUTO_INCREMENT NOT NULL, json_data LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fiche_matiere_parcours DROP FOREIGN KEY FK_ED4EE92E6E38C0DB');
        $this->addSql('ALTER TABLE fiche_matiere_parcours DROP FOREIGN KEY FK_ED4EE92E5A3AD6AF');
        $this->addSql('DROP TABLE fiche_matiere_parcours');
        $this->addSql('ALTER TABLE adresse CHANGE adresse1 adresse1 VARCHAR(255) DEFAULT NULL, CHANGE adresse2 adresse2 VARCHAR(255) DEFAULT NULL, CHANGE code_postal code_postal VARCHAR(30) DEFAULT NULL, CHANGE ville ville VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE annee_universitaire CHANGE date_ouverture_dpe date_ouverture_dpe DATETIME DEFAULT NULL, CHANGE date_cloture_dpe date_cloture_dpe DATETIME DEFAULT NULL, CHANGE date_transmission_ses date_transmission_ses DATETIME DEFAULT NULL, CHANGE date_cfvu date_cfvu DATETIME DEFAULT NULL, CHANGE date_publication date_publication DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE but_apprentissage_critique CHANGE libelle libelle LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE but_competence CHANGE nom_court nom_court VARCHAR(40) DEFAULT NULL, CHANGE situations situations JSON DEFAULT NULL, CHANGE composantes composantes JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE but_niveau CHANGE annee annee VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE composante CHANGE tel_standard tel_standard VARCHAR(10) DEFAULT NULL, CHANGE tel_complementaire tel_complementaire VARCHAR(10) DEFAULT NULL, CHANGE mail_contact mail_contact VARCHAR(255) DEFAULT NULL, CHANGE url_site url_site VARCHAR(255) DEFAULT NULL, CHANGE etat_composante etat_composante JSON DEFAULT NULL, CHANGE sigle sigle VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE element_constitutif CHANGE ects ects DOUBLE PRECISION DEFAULT NULL, CHANGE volume_cm_presentiel volume_cm_presentiel DOUBLE PRECISION DEFAULT NULL, CHANGE volume_td_presentiel volume_td_presentiel DOUBLE PRECISION DEFAULT NULL, CHANGE volume_tp_presentiel volume_tp_presentiel DOUBLE PRECISION DEFAULT NULL, CHANGE volume_cm_distanciel volume_cm_distanciel DOUBLE PRECISION DEFAULT NULL, CHANGE volume_td_distanciel volume_td_distanciel DOUBLE PRECISION DEFAULT NULL, CHANGE volume_tp_distanciel volume_tp_distanciel DOUBLE PRECISION DEFAULT NULL, CHANGE texte_ec_libre texte_ec_libre VARCHAR(255) DEFAULT NULL, CHANGE libelle libelle VARCHAR(255) DEFAULT NULL, CHANGE type_mccc type_mccc VARCHAR(20) DEFAULT NULL, CHANGE etat_mccc etat_mccc VARCHAR(255) DEFAULT NULL, CHANGE volume_te volume_te DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE etablissement CHANGE options options JSON NOT NULL');
        $this->addSql('ALTER TABLE fiche_matiere CHANGE libelle_anglais libelle_anglais VARCHAR(250) DEFAULT NULL, CHANGE etat_steps etat_steps JSON NOT NULL, CHANGE sigle sigle VARCHAR(255) DEFAULT NULL, CHANGE type_matiere type_matiere VARCHAR(20) DEFAULT NULL, CHANGE volume_cm_presentiel volume_cm_presentiel DOUBLE PRECISION DEFAULT NULL, CHANGE volume_td_presentiel volume_td_presentiel DOUBLE PRECISION DEFAULT NULL, CHANGE volume_tp_presentiel volume_tp_presentiel DOUBLE PRECISION DEFAULT NULL, CHANGE volume_te volume_te DOUBLE PRECISION DEFAULT NULL, CHANGE etat_mccc etat_mccc VARCHAR(255) DEFAULT NULL, CHANGE volume_cm_distanciel volume_cm_distanciel DOUBLE PRECISION DEFAULT NULL, CHANGE volume_td_distanciel volume_td_distanciel DOUBLE PRECISION DEFAULT NULL, CHANGE volume_tp_distanciel volume_tp_distanciel DOUBLE PRECISION DEFAULT NULL, CHANGE type_mccc type_mccc VARCHAR(10) DEFAULT NULL, CHANGE ects ects DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE formation CHANGE mention_texte mention_texte VARCHAR(255) DEFAULT NULL, CHANGE code_rncp code_rncp VARCHAR(10) DEFAULT NULL, CHANGE regime_inscription regime_inscription JSON DEFAULT NULL, CHANGE structure_semestres structure_semestres JSON DEFAULT NULL, CHANGE etat_dpe etat_dpe JSON DEFAULT NULL, CHANGE etat_steps etat_steps JSON NOT NULL, CHANGE sigle sigle VARCHAR(255) DEFAULT NULL, CHANGE remplissage remplissage JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE formation_demande CHANGE date_validation_dpe date_validation_dpe DATETIME DEFAULT NULL, CHANGE mention_texte mention_texte VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE historique CHANGE date date DATETIME DEFAULT NULL, CHANGE complements complements JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE mccc CHANGE pourcentage pourcentage DOUBLE PRECISION NOT NULL, CHANGE type_epreuve type_epreuve JSON DEFAULT NULL, CHANGE duree duree TIME DEFAULT NULL, CHANGE options options JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE mention CHANGE sigle sigle VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE notification CHANGE options options VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE parcours CHANGE nb_heures_stages nb_heures_stages DOUBLE PRECISION DEFAULT NULL, CHANGE nb_heures_projet nb_heures_projet DOUBLE PRECISION DEFAULT NULL, CHANGE codes_rome codes_rome JSON DEFAULT NULL, CHANGE regime_inscription regime_inscription JSON DEFAULT NULL, CHANGE nb_heures_situation_pro nb_heures_situation_pro DOUBLE PRECISION DEFAULT NULL, CHANGE sigle sigle VARCHAR(15) DEFAULT NULL, CHANGE etat_steps etat_steps JSON NOT NULL, CHANGE etat_parcours etat_parcours JSON DEFAULT NULL, CHANGE remplissage remplissage JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE role CHANGE droits droits JSON DEFAULT NULL, CHANGE porte porte VARCHAR(10) DEFAULT NULL, CHANGE centre centre VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE type_diplome CHANGE libelle_court libelle_court VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE type_epreuve CHANGE sigle sigle VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE ue CHANGE libelle libelle VARCHAR(255) DEFAULT NULL, CHANGE ects ects DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE date_valide_dpe date_valide_dpe DATETIME DEFAULT NULL, CHANGE date_valide_administration date_valide_administration DATETIME DEFAULT NULL, CHANGE date_demande date_demande DATETIME DEFAULT NULL, CHANGE civilite civilite VARCHAR(50) DEFAULT NULL, CHANGE tel_fixe tel_fixe VARCHAR(10) DEFAULT NULL, CHANGE tel_portable tel_portable VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_centre CHANGE droits droits JSON NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fiche_matiere_parcours (id INT AUTO_INCREMENT NOT NULL, fiche_matiere_id INT DEFAULT NULL, parcours_id INT DEFAULT NULL, INDEX IDX_ED4EE92E5A3AD6AF (fiche_matiere_id), INDEX IDX_ED4EE92E6E38C0DB (parcours_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE fiche_matiere_parcours ADD CONSTRAINT FK_ED4EE92E6E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE fiche_matiere_parcours ADD CONSTRAINT FK_ED4EE92E5A3AD6AF FOREIGN KEY (fiche_matiere_id) REFERENCES fiche_matiere (id)');
        $this->addSql('DROP TABLE parcours_versioning');
        $this->addSql('ALTER TABLE adresse CHANGE adresse1 adresse1 VARCHAR(255) DEFAULT \'NULL\', CHANGE adresse2 adresse2 VARCHAR(255) DEFAULT \'NULL\', CHANGE code_postal code_postal VARCHAR(30) DEFAULT \'NULL\', CHANGE ville ville VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE annee_universitaire CHANGE date_ouverture_dpe date_ouverture_dpe DATETIME DEFAULT \'NULL\', CHANGE date_cloture_dpe date_cloture_dpe DATETIME DEFAULT \'NULL\', CHANGE date_transmission_ses date_transmission_ses DATETIME DEFAULT \'NULL\', CHANGE date_cfvu date_cfvu DATETIME DEFAULT \'NULL\', CHANGE date_publication date_publication DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE but_apprentissage_critique CHANGE libelle libelle LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE but_competence CHANGE nom_court nom_court VARCHAR(50) DEFAULT \'NULL\', CHANGE situations situations JSON DEFAULT \'NULL\', CHANGE composantes composantes JSON DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE but_niveau CHANGE annee annee VARCHAR(10) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE composante CHANGE tel_standard tel_standard VARCHAR(10) DEFAULT \'NULL\', CHANGE tel_complementaire tel_complementaire VARCHAR(10) DEFAULT \'NULL\', CHANGE mail_contact mail_contact VARCHAR(255) DEFAULT \'NULL\', CHANGE url_site url_site VARCHAR(255) DEFAULT \'NULL\', CHANGE etat_composante etat_composante JSON DEFAULT \'NULL\', CHANGE sigle sigle VARCHAR(20) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE element_constitutif CHANGE ects ects DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_cm_presentiel volume_cm_presentiel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_td_presentiel volume_td_presentiel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_tp_presentiel volume_tp_presentiel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_cm_distanciel volume_cm_distanciel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_td_distanciel volume_td_distanciel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_tp_distanciel volume_tp_distanciel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE texte_ec_libre texte_ec_libre VARCHAR(255) DEFAULT \'NULL\', CHANGE libelle libelle VARCHAR(255) DEFAULT \'NULL\', CHANGE type_mccc type_mccc VARCHAR(20) DEFAULT \'NULL\', CHANGE etat_mccc etat_mccc VARCHAR(255) DEFAULT \'NULL\', CHANGE volume_te volume_te DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE etablissement CHANGE options options LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE fiche_matiere CHANGE libelle_anglais libelle_anglais VARCHAR(250) DEFAULT \'NULL\', CHANGE etat_steps etat_steps JSON NOT NULL, CHANGE sigle sigle VARCHAR(255) DEFAULT \'NULL\', CHANGE type_matiere type_matiere VARCHAR(20) DEFAULT \'NULL\', CHANGE volume_cm_presentiel volume_cm_presentiel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_td_presentiel volume_td_presentiel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_tp_presentiel volume_tp_presentiel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_te volume_te DOUBLE PRECISION DEFAULT \'NULL\', CHANGE etat_mccc etat_mccc VARCHAR(255) DEFAULT \'NULL\', CHANGE volume_cm_distanciel volume_cm_distanciel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_td_distanciel volume_td_distanciel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE volume_tp_distanciel volume_tp_distanciel DOUBLE PRECISION DEFAULT \'NULL\', CHANGE type_mccc type_mccc VARCHAR(10) DEFAULT \'NULL\', CHANGE ects ects DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE formation CHANGE mention_texte mention_texte VARCHAR(255) DEFAULT \'NULL\', CHANGE code_rncp code_rncp VARCHAR(10) DEFAULT \'NULL\', CHANGE regime_inscription regime_inscription JSON DEFAULT \'NULL\', CHANGE structure_semestres structure_semestres JSON DEFAULT \'NULL\', CHANGE etat_dpe etat_dpe JSON DEFAULT \'NULL\', CHANGE etat_steps etat_steps JSON NOT NULL, CHANGE sigle sigle VARCHAR(255) DEFAULT \'NULL\', CHANGE remplissage remplissage LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE formation_demande CHANGE date_validation_dpe date_validation_dpe DATETIME DEFAULT \'NULL\', CHANGE mention_texte mention_texte VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE historique CHANGE date date DATETIME DEFAULT \'NULL\', CHANGE complements complements JSON DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE mccc CHANGE pourcentage pourcentage DOUBLE PRECISION DEFAULT \'NULL\', CHANGE type_epreuve type_epreuve JSON DEFAULT \'NULL\', CHANGE duree duree TIME DEFAULT \'NULL\', CHANGE options options LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE mention CHANGE sigle sigle VARCHAR(20) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE notification CHANGE options options VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE parcours CHANGE nb_heures_stages nb_heures_stages DOUBLE PRECISION DEFAULT \'NULL\', CHANGE nb_heures_projet nb_heures_projet DOUBLE PRECISION DEFAULT \'NULL\', CHANGE codes_rome codes_rome JSON DEFAULT \'NULL\', CHANGE regime_inscription regime_inscription JSON DEFAULT \'NULL\', CHANGE nb_heures_situation_pro nb_heures_situation_pro DOUBLE PRECISION DEFAULT \'NULL\', CHANGE sigle sigle VARCHAR(15) DEFAULT \'NULL\', CHANGE etat_steps etat_steps JSON NOT NULL, CHANGE etat_parcours etat_parcours JSON DEFAULT \'NULL\', CHANGE remplissage remplissage LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE role CHANGE droits droits JSON DEFAULT \'NULL\', CHANGE porte porte VARCHAR(10) DEFAULT \'NULL\', CHANGE centre centre VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE type_diplome CHANGE libelle_court libelle_court VARCHAR(50) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE type_epreuve CHANGE sigle sigle VARCHAR(20) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE ue CHANGE libelle libelle VARCHAR(255) DEFAULT \'NULL\', CHANGE ects ects DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles JSON NOT NULL, CHANGE password password VARCHAR(255) DEFAULT \'NULL\', CHANGE date_valide_dpe date_valide_dpe DATETIME DEFAULT \'NULL\', CHANGE date_valide_administration date_valide_administration DATETIME DEFAULT \'NULL\', CHANGE date_demande date_demande DATETIME DEFAULT \'NULL\', CHANGE civilite civilite VARCHAR(50) DEFAULT \'NULL\', CHANGE tel_fixe tel_fixe VARCHAR(10) DEFAULT \'NULL\', CHANGE tel_portable tel_portable VARCHAR(10) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user_centre CHANGE droits droits JSON NOT NULL');
    }
}
