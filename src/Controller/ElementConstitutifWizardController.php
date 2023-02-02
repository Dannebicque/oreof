<?php

namespace App\Controller;

use App\Entity\ElementConstitutif;
use App\Entity\Formation;

use App\Form\EcStep1Type;
use App\Form\EcStep2Type;
use App\Form\EcStep3Type;
use App\Form\EcStep4Type;
use App\Form\EcStep5Type;
use App\Repository\BlocCompetenceRepository;
use App\Repository\LangueRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
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

    #[Route('/{ec}/1', name: 'app_ec_wizard_step_1', methods: ['GET'])]
    public function step1(ElementConstitutif $ec): Response
    {
        $form = $this->createForm(EcStep1Type::class, $ec);

        return $this->render('element_constitutif_wizard/_step1.html.twig', [
            'form' => $form->createView(),
            'ec' => $ec,
        ]);
    }

    #[Route('/{ec}/2', name: 'app_ec_wizard_step_2', methods: ['GET'])]
    public function step2(
        ElementConstitutif $ec): Response
    {
        $form = $this->createForm(EcStep2Type::class, $ec);


        return $this->render('element_constitutif_wizard/_step2.html.twig', [
            'form' => $form->createView(),
            'ec' => $ec,
        ]);
    }

    #[Route('/{ec}/3', name: 'app_ec_wizard_step_3', methods: ['GET'])]
    public function step3(
        BlocCompetenceRepository $blocCompetenceRepository,
        ElementConstitutif $ec
    ): Response {
        $form = $this->createForm(EcStep3Type::class, $ec);

        return $this->render('element_constitutif_wizard/_step3.html.twig', [
            'ec' => $ec,
            'form' => $form->createView(),
            'bcs' => $blocCompetenceRepository->findBy(['formation' => $ec->getFormation()]),
        ]);
    }

    #[Route('/{ec}/4', name: 'app_ec_wizard_step_4', methods: ['GET'])]
    public function step4(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        ElementConstitutif $ec
    ): Response {
        $form = $this->createForm(EcStep4Type::class, $ec);//tood: le formulaire si les droits du responsable de formation ou plus ?
        //pas si responsable EC

        return $this->render('element_constitutif_wizard/_step4.html.twig', [
            'ec' => $ec,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{ec}/5', name: 'app_ec_wizard_step_5', methods: ['GET'])]
    public function step5(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        ElementConstitutif $ec
    ): Response {
        $form = $this->createForm(EcStep5Type::class, $ec); //todo: simple affichage selon les droits ?

        return $this->render('element_constitutif_wizard/_step4.html.twig', [
            'ec' => $ec,
            'form' => $form->createView()
        ]);
    }
}
