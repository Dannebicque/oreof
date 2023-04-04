<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Structure/SemestreController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Structure;

use _PHPStan_59e3e945c\Nette\Utils\Json;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Repository\SemestreParcoursRepository;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/semestre', name: 'structure_semestre_')
]
class SemestreController extends AbstractController
{
    #[
        Route('/detail/parcours/{parcours}', name: 'detail_parcours')
    ]
    public function detailParcours(
        SemestreParcoursRepository $semestreRepository,
        Parcours $parcours
    ): Response
    {
        $semestres = $semestreRepository->findBy(['parcours' => $parcours]);//todo: filtrer selon droits // ajouter le tronc commun

        return $this->render('structure/semestre/_liste.html.twig', [
            'semestres' => $semestres,
            'parcours' => $parcours
        ]);
    }

    #[
        Route('/edit/{semestre}/{parcours}', name: 'edit')
    ]
    public function edit(
        SemestreParcoursRepository $semestreRepository,
        Semestre $semestre,
        Parcours $parcours
    ): Response
    {
        return $this->render('structure/semestre/_edit.html.twig', [
            'semestre' => $semestre,
            'parcours' => $parcours
        ]);
    }

    #[
        Route('/data/{semestre}/{parcours}', name: 'data')
    ]
    public function data(
        Request $request,
        SemestreParcoursRepository $semestreRepository,
        Semestre $semestre,
        Parcours $parcours
    ): Response
    {
        $data = JsonRequest::getFromRequest($request);

        switch($data['action']) {
            case 'mutualise':
                return $this->render('structure/semestre/_mutualise.html.twig', [
                    'semestre' => $semestre,
                    'parcours' => $parcours
                ]);
                break;
            case 'reutilise':
                return $this->render('structure/semestre/_reutilise.html.twig', [
                    'semestre' => $semestre,
                    'parcours' => $parcours
                ]);
                break;
        }
    }
}
