<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Structure/EcController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Structure;

use App\Controller\BaseController;
use App\Entity\Parcours;
use App\Entity\Ue;
use App\Repository\EcUeRepository;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/element-constitutif', name: 'structure_ec_')
]
class EcController extends BaseController
{
    #[
        Route('/', name: 'index')
    ]
    public function index(): Response
    {
        return $this->render('structure/ec/index.html.twig');
    }

    #[
        Route('/detail/ue/{ue}/{parcours}', name: 'detail_ue')
    ]
    public function detailComposante(
        EcUeRepository $ecUeRepository,
        Ue $ue,
        Parcours $parcours
    ): Response {
        $ecs = $ecUeRepository->findByUe($ue);

        return $this->render('structure/ec/_liste.html.twig', [
            'ecs' => $ecs,
            'ue' => $ue,
            'parcours' => $parcours,
            'deplacer' => true,
            'mode' => 'detail'
        ]);
    }
}
