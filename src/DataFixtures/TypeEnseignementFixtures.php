<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DataFixtures/TypeEnseignementFixtures.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 02/02/2023 13:26
 */

namespace App\DataFixtures;

use App\Entity\TypeEnseignement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeEnseignementFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $te = new TypeEnseignement();
        $te->setLibelle('Obligatoire');
        $manager->persist($te);

        $te = new TypeEnseignement();
        $te->setLibelle('1 option obligatoire au choix');
        $manager->persist($te);

        $manager->flush();
    }
}
