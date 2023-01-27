<?php

namespace App\DataFixtures;

use App\Entity\AnneeUniversitaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AnneeUniversitaireFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $annee = new AnneeUniversitaire();
        $annee->setLibelle('2024-2025');
        $annee->setAnnee(2024);
        $annee->setDefaut(true);
        $manager->persist($annee);

        $manager->flush();
    }
}
