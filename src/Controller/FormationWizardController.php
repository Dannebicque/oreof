<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationStep1Type;
use App\Form\FormationStep2Type;
use App\Form\FormationStep3Type;
use App\Form\FormationStep4Type;
use App\Form\FormationStep5Type;
use App\Form\FormationStep6Type;
use App\Form\FormationStep7Type;
use App\Form\FormationStep8Type;
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
    public function step3(Formation $formation): Response
    {
        return $this->render('formation_wizard/_step3.html.twig', [
            'formation' => $formation,
            'blocCompetences' => $formation->getBlocCompetences(),
        ]);
    }

    #[Route('/{formation}/4', name: 'app_formation_wizard_step_4', methods: ['GET'])]
    public function step4(Formation $formation): Response
    {
        $form = $this->createForm(FormationStep4Type::class, $formation);


        return $this->render('formation_wizard/_step4.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,

        ]);
    }

    #[Route('/{formation}/5', name: 'app_formation_wizard_step_5', methods: ['GET'])]
    public function step5(Formation $formation): Response
    {
        $form = $this->createForm(FormationStep5Type::class, $formation);

        return $this->render('formation_wizard/_step5.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,

        ]);
    }

    #[Route('/{formation}/6', name: 'app_formation_wizard_step_6', methods: ['GET'])]
    public function step6(Formation $formation): Response
    {
        $form = $this->createForm(FormationStep6Type::class, $formation);

        return $this->render('formation_wizard/_step6.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,

        ]);
    }

    #[Route('/{formation}/7', name: 'app_formation_wizard_step_7', methods: ['GET'])]
    public function step7(Formation $formation): Response
    {
        $form = $this->createForm(FormationStep7Type::class, $formation);


        return $this->render('formation_wizard/_step7.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,

        ]);
    }

    #[Route('/{formation}/8', name: 'app_formation_wizard_step_8', methods: ['GET'])]
    public function step8(Formation $formation): Response
    {
        $form = $this->createForm(FormationStep8Type::class, $formation);

        return $this->render('formation_wizard/_step8.html.twig', [
            'form' => $form->createView(),
            'formation' => $formation,
        ]);
    }
}
