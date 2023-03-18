<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Structure/ParcoursController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Structure;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Repository\ParcoursRepository;
use App\Repository\SemestreParcoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/parcours', name: 'structure_parcours_')
]
class ParcoursController extends AbstractController
{
    #[
        Route('/', name: 'index')
    ]
    public function index(): Response
    {
        return $this->render('structure/parcours/index.html.twig');
    }

    #[
        Route('/liste', name: 'liste')
    ]
    public function liste(): Response
    {
        return $this->render('structure/parcours/_liste.html.twig');
    }

    #[
        Route('/detail/formation/{formation}', name: 'detail_formation')
    ]
    public function detailFormation(
        ParcoursRepository $parcoursRepository,
        Formation $formation
    ): Response
    {
        $parcours = $parcoursRepository->findBy(['formation' => $formation]);//todo: filtrer selon droits ? Ajouter les co-portées ? avec une mise en valeur et sans édition ? si resp DPE


        return $this->render('structure/parcours/_liste.html.twig', [
            'parcours' => $parcours
        ]);
    }

    #[Route('/detail/parcours/{parcours}', name: 'detail_formation_parcours')]
    public function detailFormationTroncCommun(
        SemestreParcoursRepository $semestreRepository,
        Parcours $parcours
    ): Response
    {
        $semestres = $semestreRepository->findByParcours($parcours);

        return $this->render('structure/semestre/_liste.html.twig', [
            'semestres' => $semestres,
            'parcours' => $parcours,
            'hasParcours' => $parcours->getFormation()?->isHasParcours()
        ]);
    }
}
