<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/DataFixtures/AnneeUniversitaireFixtures.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 27/01/2023 19:12
 */

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
