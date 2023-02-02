<?php

namespace App\Controller\Structure;

use App\Entity\Formation;
use App\Repository\ParcoursRepository;
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
        return $this->render('structure/parcours/index.html.twig', [

        ]);
    }

    #[
        Route('/liste', name: 'liste')
    ]
    public function liste(): Response
    {
        return $this->render('structure/parcours/_liste.html.twig', [
        ]);
    }

    #[
        Route('/detail/formation/{formation}', name: 'detail_formation')
    ]
    public function detailFormation(
        ParcoursRepository $parcoursRepository,
        Formation $formation): Response
    {
        $parcours = $parcoursRepository->findBy(['formation' => $formation]);//todo: filtrer selon droits ? Ajouter les co-portées ? avec une mise en valeur et sans édition ? si resp DPE


        return $this->render('structure/parcours/_liste.html.twig', [
            'parcours' => $parcours
        ]);
    }



}
