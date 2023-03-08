<?php

namespace App\Classes;

use App\Entity\ElementConstitutif;
use App\Entity\Ue;
use App\Repository\EcUeRepository;
use App\Repository\ElementConstitutifRepository;
use Doctrine\ORM\EntityManagerInterface;

class EcOrdre
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EcUeRepository $ecUeRepository
    ) {
    }


    public function getOrdreSuivant(Ue $ue): int
    {
        $ordreMax = $this->ecUeRepository->findLastEc($ue);

        return $ordreMax[0]['ordreMax'] === null ? 1 : ++$ordreMax[0]['ordreMax'];
    }

    public function deplacerElementConstitutif(ElementConstitutif $elementConstitutif, string $sens, Ue $ue)
    {
        //modifie l'ordre de la ressource
        $ordreInitial = $elementConstitutif->getOrdre();

        if ($sens === 'up') {
            $ordreDestination = $ordreInitial - 1;
        } else {
            $ordreDestination = $ordreInitial + 1;
        }

        //récupère toutes les ressources à déplacer
        return $this->inverseEc($ordreInitial, $ordreDestination, $elementConstitutif, $ue);
    }

    private function inverseEc(
        ?int $ordreInitial,
        ?int $ordreDestination,
        ElementConstitutif $elementConstitutif,
        Ue $ue
    ): bool {
        $ecs = $this->ecUeRepository->findByUeOrdre($ordreDestination, $ue);
        $elementConstitutif->setOrdre($ordreDestination);
        $elementConstitutif->genereCode();


        if ($ecs !== null) {
            $ecs->getEc()->setOrdre($ordreInitial);
            $ecs->getEc()->genereCode();
        }

        $this->entityManager->flush();

        return true;
    }
}
