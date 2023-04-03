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

        if ($ordreInitial === 1 && $sens === 'up') {
            return false;
        }

        if ($sens === 'up') {
            $ordreDestination = $ordreInitial - 1;
        } else {
            $ordreDestination = $ordreInitial + 1;
        }

        //récupère toutes les ressources à déplacer
        return $this->inverseEc($ordreInitial, $ordreDestination, $elementConstitutif, $ue);
    }

    public function deplacerSubElementConstitutif(ElementConstitutif $elementConstitutif, string $sens, Ue $ue): bool
    {
        //modifie l'ordre de la ressource
        $ordreInitial = $elementConstitutif->getSubOrdre();

        if ($ordreInitial === 1 && $sens === 'up') {
            return false;
        }

        if ($sens === 'up') {
            $ordreDestination = $ordreInitial - 1;
        } else {
            $ordreDestination = $ordreInitial + 1;
        }

        //récupère toutes les ressources à déplacer
        return $this->inverseSubOrdreEc($ordreInitial, $ordreDestination, $elementConstitutif, $ue);
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

        foreach ($ecs as $ec) {
            $ec->setOrdre($ordreInitial);
            $ec->genereCode();
        }

        $this->entityManager->flush();

        return true;
    }

    private function inverseSubOrdreEc(
        ?int $ordreInitial,
        ?int $ordreDestination,
        ElementConstitutif $elementConstitutif,
        Ue $ue
    ): bool {
        $elementConstitutif->setSubOrdre($ordreDestination);
        $elementConstitutif->genereCode();
        $ec = $this->elementConstitutifRepository->findByUeSubOrdre(
            $ordreDestination,
            $ue,
            $elementConstitutif->getOrdre()
        );
        if ($ec !== null) {
            $ec->setSubOrdre($ordreInitial);
            $ec->genereCode();
        }

        $this->entityManager->flush();

        return true;
    }

    public function removeElementConstitutif(?int $ordre, ?Ue $ue): void
    {
        //récupérer les EC à décaler
        $ecs = $this->elementConstitutifRepository->findByUeOrdreSup($ordre, $ue);
        foreach ($ecs as $ec) {
            $ec->setOrdre($ec->getOrdre() - 1);
            $ec->genereCode();
        }
        $this->entityManager->flush();
    }
}
