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
use App\Repository\EcUeRepository;
use App\Repository\ElementConstitutifRepository;
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

        if ($sens === 'up') {
            $ordreDestination = $ordreInitial - 1;
        } else {
            $ordreDestination = $ordreInitial + 1;
        }

        //récupère toutes les ressources à déplacer
        return $this->inverseUe($ordreInitial, $ordreDestination, $ue);
    }

    private function inverseUe(
        ?int $ordreInitial,
        ?int $ordreDestination,
        Ue $ue,
    ): bool {
        $ues = $this->ueRepository->findBySemestreOrdre($ordreDestination, $ue->getSemestre());
        $ue->setOrdre($ordreDestination);
        //$ue->genereCode();
        //todo: mettre à jour les EC

        if ($ues !== null) {
            $ues->setOrdre($ordreInitial);
            //$ues->genereCode();
            //todo: mettre à jour les EC
        }

        $this->entityManager->flush();

        return true;
    }
}
