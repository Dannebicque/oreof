<?php

namespace App\Controller\API;

use App\Entity\Parcours;
use App\Entity\Ue;
use App\Repository\ComposanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EctsController extends AbstractController
{
    #[Route('/api/ects/ue/{ue}/{parcours}', name: 'api_ects_ue')]
    public function getComposante(
        Ue $ue,
        Parcours $parcours,
    ): Response
    {
        $totalEctsUe = 0;
        $ectsSemestre = 0;

        $ecsInUe = $ue->getEcUes();
        foreach ($ecsInUe as $ec) {
            $totalEctsUe += $ec->getEc()?->getEcts();
        }

        $semestre = $ue->getSemestre();
        $uesInSemestre = $semestre->getUes();

        foreach ($uesInSemestre as $u) {
            $ecsInUe = $u->getEcUes();
            foreach ($ecsInUe as $ec) {
                $ectsSemestre += $ec->getEc()?->getEcts();
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
