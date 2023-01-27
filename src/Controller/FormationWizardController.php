<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationStep1Type;
use App\Form\FormationStep2Type;
use App\TypeDiplome\TypeDiplomeRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/formation/step')]
class FormationWizardController extends AbstractController
{
    #[Route('/', name: 'app_formation_wizard', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('formation/index.html.twig', [
        ]);
    }

    #[Route('/{formation}/1', name: 'app_formation_wizard_step_1', methods: ['GET'])]
    public function step1(Formation $formation): Response
    {
        $form = $this->createForm(FormationStep1Type::class, $formation);

        return $this->render('formation_wizard/_step1.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }

    #[Route('/{formation}/2', name: 'app_formation_wizard_step_2', methods: ['GET'])]
    public function step2(Formation $formation): Response
    {
        $form = $this->createForm(FormationStep2Type::class, $formation);


        return $this->render('formation_wizard/_step2.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,

        ]);
    }

    #[Route('/{formation}/3', name: 'app_formation_wizard_step_3', methods: ['GET'])]
    public function step3(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Formation $formation
    ): Response {
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());

        return $this->render('formation_wizard/_step3.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
        ]);
    }

    #[Route('/{formation}/4', name: 'app_formation_wizard_step_4', methods: ['GET'])]
    public function step4(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Formation $formation
    ): Response {
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());

        return $this->render('formation_wizard/_step4.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
        ]);
    }
}
