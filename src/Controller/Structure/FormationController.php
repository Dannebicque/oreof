<?php

namespace App\Controller\Structure;

use App\Entity\Composante;
use App\Entity\Formation;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/formation', name: 'structure_formation_')
]
class FormationController extends AbstractController
{
    #[
        Route('/', name: 'index')
    ]
    public function index(): Response
    {
        return $this->render('structure/formation/index.html.twig', [

        ]);
    }

    #[
        Route('/liste', name: 'liste')
    ]
    public function liste(
        FormationRepository $formationRepository
    ): Response
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
        Composante $composante): Response
    {
        $formations = $formationRepository->findBy(['composantePorteuse' => $composante]);//todo: filtrer selon droits ? Ajouter les co-portées ? avec une mise en valeur et sans édition ? si resp DPE


        return $this->render('structure/formation/_liste.html.twig', [
            'formations' => $formations
        ]);
    }

    #[Route('/validation/{formation}', name: 'modal_validation')]
    public function validation(
        FormationRepository $formationRepository,
        Formation $formation): Response
    {
        //check des différents droits
        //check si la formation est valide
        //check si les champs sont complets
        //affiche la synthèse de la formation
        return $this->render('structure/formation/_modal_validate.html.twig', [
            'formation' => $formation
        ]);
    }

    #[Route('/validate/{formation}', name: 'modal_validate')]
    public function validate(
        FormationRepository $formationRepository,
        Formation $formation): Response
    {
        //avance le workflow
        //écouter l'évent pour envoyer un mail
        //changer l'état de la formation avec le workflow
        //bloquer la modif

    }
}
