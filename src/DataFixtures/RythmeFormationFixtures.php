<?php

namespace App\DataFixtures;

use App\Entity\RythmeFormation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RythmeFormationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // génére les données pour les rythmes de formations suivants
        $rythme = new RythmeFormation();
        $rythme->setLibelle('Temps plein');
        $manager->persist($rythme);

        $rythme = new RythmeFormation();
        $rythme->setLibelle('Temps partiel');
        $manager->persist($rythme);

        $rythme = new RythmeFormation();
        $rythme->setLibelle('Cours du soir');
        $manager->persist($rythme);

        $rythme = new RythmeFormation();
        $rythme->setLibelle('Summer School');
        $manager->persist($rythme);

        $manager->flush();
    }
}
