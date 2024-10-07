<?php

namespace App\Tests;

use App\Entity\Parcours;
use App\Service\ParcoursCopyData;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParcoursCopyDataTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    private ParcoursCopyData $parcoursCopyData;

    protected function setUp(): void
    {
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->parcoursCopyData = $container->get(ParcoursCopyData::class);
    }

    public function testDTOAreSameAfterCopy(): void
    {
        $kernel = self::bootKernel();
        
        // retrieve data
        $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById(405);
        $dtoBefore = $this->parcoursCopyData->getDTOForParcours($parcours);
        // perform copy
        $this->parcoursCopyData->copyDataForParcours($parcours);
        // compare new and old data
        $dtoAfter = $this->parcoursCopyData->getDTOForParcours($parcours, heuresSurFicheMatiere: true);

        $this->assertEquals(
            $dtoBefore->heuresEctsFormation->sommeFormationTotalPres(),
            $dtoAfter->heuresEctsFormation->sommeFormationTotalPres(),
            "Le total des heures de la formation en présentiel n'est pas égal."
        );

        $this->assertEquals(
            $dtoBefore->heuresEctsFormation->sommeFormationTotalDist(),
            $dtoAfter->heuresEctsFormation->sommeFormationTotalDist(),
            "Le total des heures de la formation en distanciel n'est pas égal."
        );
    }
}
