<?php

namespace App\DataFixtures;

use App\Entity\Composante;
use App\Entity\Domaine;
use App\Entity\Etablissement;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StructureEtablissementFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $etablissement = new Etablissement();
        $etablissement->setLibelle('Université de Reims Champagne-Ardenne');
        //$etablissement->setSigle('URCA');
        //$etablissement->setAdresse('1 rue du Doyen-Gabriel-Marty');
        //$etablissement->setCodePostal('51100');
        //$etablissement->setVille('Reims');
        //$etablissement->setTelephone('03 26 51 51 51');
        $manager->persist($etablissement);

        $site = new Ville();
        $site->setLibelle('Reims');
        $site->setEtablissement($etablissement);
        $manager->persist($site);

        $site = new Ville();
        $site->setLibelle('Châlons-en-Champagne');
        $site->setEtablissement($etablissement);
        $manager->persist($site);

        $site = new Ville();
        $site->setLibelle('Troyes');
        $site->setEtablissement($etablissement);
        $manager->persist($site);

        $site = new Ville();
        $site->setLibelle('Charleville-Mézières');
        $site->setEtablissement($etablissement);
        $manager->persist($site);

        $domaine = new Domaine();
        $domaine->setLibelle('Sciences et Technologies');
        $domaine->setSigle('ST');
        $this->addReference('domaine_st', $domaine);
        $manager->persist($domaine);

        $domaine = new Domaine();
        $domaine->setLibelle('Santé');
        $domaine->setSigle('Santé');
        $manager->persist($domaine);

        $domaine = new Domaine();
        $domaine->setLibelle('Sciences Humaines et Sociales');
        $domaine->setSigle('SHS');
        $this->addReference('domaine_shs', $domaine);
        $manager->persist($domaine);

        $composante = new Composante();
        $composante->setLibelle('Faculté des Sciences');
        $composante->setDirecteur($this->getReference(UsersFixtures::RESPONSABLE_DPE_COMPOSANTE));

        $manager->persist($composante);

        $composante = new Composante();
        $composante->setLibelle('IUT de Troyes');
        $composante->setDirecteur(null);
        $manager->persist($composante);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
        ];
    }
}
