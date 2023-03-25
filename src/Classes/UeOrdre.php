<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/EcOrdre.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:10
 */

namespace App\Classes;

use App\Entity\Ue;
use App\Repository\UeRepository;
use Doctrine\ORM\EntityManagerInterface;

class UeOrdre
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UeRepository $ueRepository
    ) {
    }

    public function deplacerUe(Ue $ue, string $sens): bool
    {
        //modifie l'ordre de la ressource
        $ordreInitial = $ue->getOrdre();

        if ($ordreInitial === 1 && $sens === 'up') {
            return false;
        }

        if ($sens === 'up') {
            $ordreDestination = $ordreInitial - 1;
        } else {
            $ordreDestination = $ordreInitial + 1;
        }

        //récupère toutes les ressources à déplacer
        return $this->inverseUe($ordreInitial, $ordreDestination, $ue);
    }

    public function deplacerSubUe(Ue $ue, string $sens): bool
    {
        //modifie l'ordre de la ressource
        $ordreInitial = $ue->getSubOrdre();

        if ($ordreInitial === 1 && $sens === 'up') {
            return false;
        }

        if ($sens === 'up') {
            $ordreDestination = $ordreInitial - 1;
        } else {
            $ordreDestination = $ordreInitial + 1;
        }

        //récupère toutes les ressources à déplacer
        return $this->inverseSubOrdreUe($ordreInitial, $ordreDestination, $ue);
    }

    private function inverseUe(
        ?int $ordreInitial,
        ?int $ordreDestination,
        Ue $ue,
    ): bool {
        // on inverse les sous-ordres
        $ue->setOrdre($ordreDestination);
        $ues = $this->ueRepository->findBySemestreOrdre($ordreDestination, $ue->getSemestre());
        foreach ($ues as $u) {
            $u->setOrdre($ordreInitial);
            foreach ($u->getElementConstitutifs() as $ec) {
                $ec->genereCode();
            }
        }

        //mise à jour des EC de l'UE de destination
        foreach ($ue->getElementConstitutifs() as $ec) {
            $ec->genereCode();
        }

        $this->entityManager->flush();

        return true;
    }

    private function inverseSubOrdreUe(
        ?int $ordreInitial,
        ?int $ordreDestination,
        Ue $ue,
    ): bool {
        // on inverse les sous-ordres
        $ues = $this->ueRepository->findBySemestreSubOrdre($ordreDestination, $ue->getSemestre(), $ue->getOrdre());
        $ue->setSubOrdre($ordreDestination);

        if ($ues !== null) {
            $ues->setSubOrdre($ordreInitial);
            foreach ($ues->getElementConstitutifs() as $ec) {
                $ec->genereCode();
            }
        }

        foreach ($ue->getElementConstitutifs() as $ec) {
            $ec->genereCode();
        }

        $this->entityManager->flush();

        return true;
    }
}
