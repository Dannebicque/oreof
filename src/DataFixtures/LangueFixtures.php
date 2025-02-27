<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DataFixtures/LangueFixtures.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\DataFixtures;

use App\Entity\Langue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LangueFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $langue = new Langue();
        $langue->setLibelle('Français');
        $langue->setCodeIso('fr');
        $manager->persist($langue);

        $langue = new Langue();
        $langue->setLibelle('Anglais');
        $langue->setCodeIso('en');
        $manager->persist($langue);

        $langue = new Langue();
        $langue->setLibelle('Espagnol');
        $langue->setCodeIso('es');
        $manager->persist($langue);

        $langue = new Langue();
        $langue->setLibelle('Allemand');
        $langue->setCodeIso('de');
        $manager->persist($langue);


        $manager->flush();
    }
}
