<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Classes/EcOrdre.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:10
 */

namespace App\Classes;

use App\Entity\Parcours;
use App\Entity\SemestreParcours;
use App\Repository\SemestreParcoursRepository;
use Doctrine\ORM\EntityManagerInterface;

class SemestreOrdre
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SemestreParcoursRepository $semestreParcoursRepository
    ) {
    }

    public function deplacerSemestre(SemestreParcours $semestre, Parcours $parcours, string $sens): bool
    {
        //modifie l'ordre de la ressource
        $ordreInitial = $semestre->getOrdre();

        if ($ordreInitial === 1 && $sens === 'up') {
            return false;
        }

        if ($sens === 'up') {
            $ordreDestination = $ordreInitial - 1;
        } else {
            $ordreDestination = $ordreInitial + 1;
        }

        //récupère toutes les ressources à déplacer
        return $this->inverseSemestre($ordreInitial, $ordreDestination, $semestre, $parcours);
    }

    private function inverseSemestre(
        ?int $ordreInitial,
        ?int $ordreDestination,
        SemestreParcours $semestre,
        Parcours $parcours
    ): bool {
        // on inverse les sous-ordres
        $semestre->setOrdre($ordreDestination);
        $semestreOld = $this->semestreParcoursRepository->findByParcoursOrdre($ordreDestination, $parcours);
        if ($semestreOld !== null) {
            $semestreOld->setOrdre($ordreInitial);
        }

        $this->entityManager->flush();

        return true;
    }
}
