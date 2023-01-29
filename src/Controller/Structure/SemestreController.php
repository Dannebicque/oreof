<?php

namespace App\Controller\Structure;

use App\Entity\Parcours;
use App\Repository\SemestreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        Parcours $parcours)
    {
        $semestres = $semestreRepository->findBy(['parcours' => $parcours]);//todo: filtrer selon droits // ajouter le tronc commun


        return $this->render('structure/semestre/_liste.html.twig', [
            'semestres' => $semestres
        ]);
    }

}
