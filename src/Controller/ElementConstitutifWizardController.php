<?php

namespace App\Controller;

use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Form\EcStep1Type;
use App\Form\EcStep2Type;
use App\Form\EcStep3Type;
use App\Form\EcStep4Type;
use App\Form\EcStep5Type;
use App\Repository\BlocCompetenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ec/step')]
class ElementConstitutifWizardController extends AbstractController
{
    #[Route('/', name: 'app_ec_wizard', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('element_constitutif/index.html.twig', [
        ]);
    }

    #[Route('/{ec}/{parcours}/1', name: 'app_ec_wizard_step_1', methods: ['GET'])]
    public function step1(ElementConstitutif $ec, Parcours $parcours): Response
    {
        $form = $this->createForm(EcStep1Type::class, $ec);

        return $this->render('element_constitutif_wizard/_step1.html.twig', [
            'form' => $form->createView(),
            'parcours' => $parcours,
            'ec' => $ec,
        ]);
    }

    #[Route('/{ec}/{parcours}/2', name: 'app_ec_wizard_step_2', methods: ['GET'])]
    public function step2(
        ElementConstitutif $ec, Parcours $parcours): Response
    {
        $form = $this->createForm(EcStep2Type::class, $ec);


        return $this->render('element_constitutif_wizard/_step2.html.twig', [
            'form' => $form->createView(),
            'parcours' => $parcours,
            'ec' => $ec,
        ]);
    }

    #[Route('/{ec}/{parcours}/3', name: 'app_ec_wizard_step_3', methods: ['GET'])]
    public function step3(
        ElementConstitutif $ec, Parcours $parcours
    ): Response {
        $form = $this->createForm(EcStep3Type::class, $ec);

        return $this->render('element_constitutif_wizard/_step3.html.twig', [
            'ec' => $ec,
            'parcours' => $parcours,
            'form' => $form->createView(),
            'bcs' => $parcours->getBlocCompetences(),
        ]);
    }

    #[Route('/{ec}/{parcours}/4', name: 'app_ec_wizard_step_4', methods: ['GET'])]
    public function step4(
        ElementConstitutif $ec, Parcours $parcours
    ): Response {
        $form = $this->createForm(EcStep4Type::class, $ec);//tood: le formulaire si les droits du responsable de formation ou plus ?
        //pas si responsable EC

        return $this->render('element_constitutif_wizard/_step4.html.twig', [
            'ec' => $ec,
            'parcours' => $parcours,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{ec}/{parcours}/5', name: 'app_ec_wizard_step_5', methods: ['GET'])]
    public function step5(
        ElementConstitutif $ec, Parcours $parcours
    ): Response {
        $form = $this->createForm(EcStep5Type::class, $ec); //todo: simple affichage selon les droits ?

        return $this->render('element_constitutif_wizard/_step4.html.twig', [
            'ec' => $ec,
            'parcours' => $parcours,
            'form' => $form->createView()
        ]);
    }
}
