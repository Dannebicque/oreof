<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/API/EctsController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\API;

use App\Classes\GetElementConstitutif;
use App\Classes\GetUeEcts;
use App\Entity\Parcours;
use App\Entity\Ue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EctsController extends AbstractController
{
    #[Route('/api/ects/ue/{ue}/{parcours}', name: 'api_ects_ue')]
    public function getComposante(
        Ue       $ue,
        Parcours $parcours,
    ): Response {
        $totalEctsUe = 0;
        $ectsSemestre = 0;
        $typeDiplome = $parcours->getTypeDiplome();
        $semestre = $ue->getSemestre();

        if ($semestre === null || $typeDiplome === null) {
            throw $this->createNotFoundException();
        }
        GetUeEcts::getEcts($ue, $parcours, $typeDiplome);


        $uesInSemestre = $semestre->getUes();

        foreach ($uesInSemestre as $u) {
            $ecsInUe = $u->getElementConstitutifs();
            foreach ($ecsInUe as $ec) {
                if ($ec->getEcParent() !== null) {
                    $ectsSemestre += $ec->getEcParent()->getEcts();
                } else {
                    $raccroche = $ec->getFicheMatiere()?->getParcours()?->getId() !== $parcours->getId();
                    if ($raccroche && $ec->isSynchroEcts()) {
                        $ects = GetElementConstitutif::getEcts($ec, $raccroche);
                        $ectsSemestre += $ects;
                    } else {
                        $ectsSemestre += $ec->getEcts();
                    }
                }
            }
        }

        return $this->json(
            [
                'ue' => $totalEctsUe,
                'semestre' => $ectsSemestre,
                'idSemestre' => $semestre->getId(),
            ]
        );
    }
}
