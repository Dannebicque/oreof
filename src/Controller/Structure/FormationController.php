<?php

namespace App\Controller\Structure;

use App\Entity\Composante;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/formation', name: 'structure_formation_')
]
class FormationController extends AbstractController
{
    #[
        Route('/', name: 'index')
    ]
    public function index()
    {
        return $this->render('structure/formation/index.html.twig', [

        ]);
    }

    #[
        Route('/liste', name: 'liste')
    ]
    public function liste(
        FormationRepository $formationRepository
    )
    {
        $formations = $formationRepository->findByRoleUser($this->getUser());

        return $this->render('structure/formation/_liste.html.twig', [
            'formations' => $formations
        ]);
    }

    #[
        Route('/detail/composante/{composante}', name: 'detail_composante')
    ]
    public function detailComposante(
        FormationRepository $formationRepository,
        Composante $composante)
    {
        $formations = $formationRepository->findBy(['composantePorteuse' => $composante]);//todo: filtrer selon droits ? Ajouter les co-portées ? avec une mise en valeur et sans édition ? si resp DPE


        return $this->render('structure/formation/_liste.html.twig', [
            'formations' => $formations
        ]);
    }
}
