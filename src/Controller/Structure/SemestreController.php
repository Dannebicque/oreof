<?php

namespace App\Controller\Structure;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Repository\SemestreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        SemestreRepository $semestreRepository,
        Parcours $parcours): Response
    {
        $semestresTc = $semestreRepository->findBy(['formation' => $parcours->getFormation()]);
        $semestres = $semestreRepository->findBy(['parcours' => $parcours]);//todo: filtrer selon droits // ajouter le tronc commun



        return $this->render('structure/semestre/_liste.html.twig', [
            'semestresTc' => $semestresTc,
            'semestres' => $semestres
        ]);
    }

    #[Route('/detail/formation/tron_commun/{formation}', name: 'detail_formation_tronc_commun')]
    public function detailFormationTroncCommun(
        SemestreRepository $semestreRepository,
        Formation $formation): Response
    {
        $semestresTc = $semestreRepository->findBy(['formation' => $formation]);

        return $this->render('structure/semestre/_liste.html.twig', [
            'semestresTc' => $semestresTc,
            'semestres' => []
        ]);
    }

}
