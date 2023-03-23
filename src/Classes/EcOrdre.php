<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/EcOrdre.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:10
 */

namespace App\Classes;

use App\Entity\ElementConstitutif;
use App\Entity\Ue;
use App\Repository\ElementConstitutifRepository;
use Doctrine\ORM\EntityManagerInterface;

class EcOrdre
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ElementConstitutifRepository $elementConstitutifRepository
    ) {
    }


    public function getOrdreSuivant(Ue $ue): int
    {
        return $this->elementConstitutifRepository->findLastEc($ue) + 1;
    }

    public function deplacerElementConstitutif(ElementConstitutif $elementConstitutif, string $sens, Ue $ue): bool
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
        $ecs = $this->elementConstitutifRepository->findByUeOrdre($ordreDestination, $ue);
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
