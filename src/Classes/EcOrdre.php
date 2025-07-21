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
use Symfony\Component\HttpFoundation\Request;

readonly class EcOrdre
{
    public function __construct(
        private EntityManagerInterface       $entityManager,
        private ElementConstitutifRepository $elementConstitutifRepository
    ) {
    }


    public function getOrdreSuivant(Ue $ue, Request $request): int
    {
        if ($request->query->has('element')) {
            $element = $this->elementConstitutifRepository->find($request->query->get('element'));
            if (null !== $element) {
                //décaler les autres

                $ecs = $ue->getElementConstitutifs();
                foreach ($ecs as $ec) {
                    if ($ec->getOrdre() >= $element->getOrdre() && $ec->getId() !== $element->getId()) {
                        $ec->setOrdre($ec->getOrdre() + 1);
                        $ec->genereCode();
                        foreach ($ec->getEcEnfants() as $ecEnfant) {
                            $ecEnfant->genereCode();
                        }
                    }
                }


                return $element->getOrdre()+1;
            }
        }

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
        if ($elementConstitutif->getEcParent() === null) {
            return $this->inverseEc($ordreInitial, $ordreDestination, $elementConstitutif, $ue);
        } else {
            return $this->inverseEcEnfant($ordreInitial, $ordreDestination, $elementConstitutif);
        }
    }

    private function inverseEc(
        ?int               $ordreInitial,
        ?int               $ordreDestination,
        ElementConstitutif $elementConstitutif,
        Ue                 $ue
    ): bool {
        $ecs = $this->elementConstitutifRepository->findByUeOrdre($ordreDestination, $ue);

        $elementConstitutif->setOrdre($ordreDestination);
        foreach ($elementConstitutif->getEcEnfants() as $ecEnfant) {
            $ecEnfant->genereCode();
        }
        $elementConstitutif->genereCode();

        foreach ($ecs as $ec) {
            $ec->setOrdre($ordreInitial);
            $ec->genereCode();
            foreach ($ec->getEcEnfants() as $ecEnfant) {
                $ecEnfant->genereCode();
            }
        }

        $this->entityManager->flush();

        return true;
    }

    private function inverseEcEnfant(
        ?int               $ordreInitial,
        ?int               $ordreDestination,
        ElementConstitutif $elementConstitutif,
    ): bool {
        $ecs = $this->elementConstitutifRepository->findByUeSubOrdre(
            $ordreDestination,
            $elementConstitutif->getEcParent()
        );

        $elementConstitutif->setOrdre($ordreDestination);
        $elementConstitutif->genereCode();

        foreach ($ecs as $ec) {
            $ec->setOrdre($ordreInitial);
            $ec->genereCode();
        }

        $this->entityManager->flush();

        return true;
    }

    public function removeElementConstitutif(ElementConstitutif $elementConstitutif): void
    {
        $ue = $elementConstitutif->getUe();
        $ecs = $ue->getElementConstitutifs();
        foreach ($ecs as $ec) {
            if ($ec->getOrdre() > $elementConstitutif->getOrdre()) {
                $ec->setOrdre($ec->getOrdre() - 1);
                $ec->genereCode();
                foreach ($ec->getEcEnfants() as $ecEnfant) {
                    $ecEnfant->genereCode();
                }
            }
        }
    }

    public function decalerEnfant(ElementConstitutif $ecParent, int $ordre): void
    {
        $ecs = $ecParent->getEcEnfants();
        foreach ($ecs as $ec) {
            if ($ec->getOrdre() >= $ordre) {
                $ec->setOrdre($ec->getOrdre() + 1);
                $ec->genereCode();
            }
        }
        $this->entityManager->flush();
    }

    public function getOrdreEnfantSuivant(ElementConstitutif $elementConstitutif): int
    {
        return $this->elementConstitutifRepository->findLastEcEnfant($elementConstitutif) + 1;
    }

    public function removeElementConstitutifEnfant(ElementConstitutif $elementConstitutif): void
    {
        $ecParent = $elementConstitutif->getEcParent();
        $ecs = $ecParent->getEcEnfants();
        foreach ($ecs as $ec) {
            if ($ec->getOrdre() > $elementConstitutif->getOrdre()) {
                $ec->setOrdre($ec->getOrdre() - 1);
                $ec->genereCode();
            }
        }
    }
}
