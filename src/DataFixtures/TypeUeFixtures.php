<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DataFixtures/TypeUeFixtures.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:16
 */

namespace App\DataFixtures;

use App\Entity\TypeUe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeUeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $typeUe = new TypeUe();
        $typeUe->setLibelle('Disciplinaire');
        $manager->persist($typeUe);

        $typeUe = new TypeUe();
        $typeUe->setLibelle('Ouverture');
        $manager->persist($typeUe);

        $typeUe = new TypeUe();
        $typeUe->setLibelle('Renforcement');
        $manager->persist($typeUe);

        $typeUe = new TypeUe();
        $typeUe->setLibelle('Outils et Langages (transversale)');
        $manager->persist($typeUe);

        $typeUe = new TypeUe();
        $typeUe->setLibelle('TER – travail d’étude et de recherche');
        $manager->persist($typeUe);

        $typeUe = new TypeUe();
        $typeUe->setLibelle('Mémoire');
        $manager->persist($typeUe);

        $typeUe = new TypeUe();
        $typeUe->setLibelle('Stage');
        $manager->persist($typeUe);

        $typeUe = new TypeUe();
        $typeUe->setLibelle('Projet tutoré');
        $manager->persist($typeUe);

        $typeUe = new TypeUe();
        $typeUe->setLibelle('Mise en situation professionnelle');
        $manager->persist($typeUe);

        $manager->flush();
    }
}
