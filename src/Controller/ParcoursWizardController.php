<?php

namespace App\Controller;

use App\Entity\Parcours;
use App\Form\ParcoursStep1Type;
use App\Form\ParcoursStep2Type;
use App\Form\ParcoursStep5Type;
use App\Form\ParcoursStep6Type;
use App\Form\ParcoursStep7Type;
use App\Form\ParcoursStep8Type;
use App\Repository\ParcoursRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/parcours/step')]
class ParcoursWizardController extends AbstractController
{
    #[Route('/', name: 'app_parcours_wizard', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('formation/index.html.twig');
    }

    #[Route('/{parcours}/1', name: 'app_parcours_wizard_step_1', methods: ['GET'])]
    public function step1(Parcours $parcours): Response
    {
        $form = $this->createForm(ParcoursStep1Type::class, $parcours);

        return $this->render('parcours_wizard/_step1.html.twig', [
            'form' => $form->createView(),
            'parcours' => $parcours,
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{parcours}/2', name: 'app_parcours_wizard_step_2', methods: ['GET'])]
    public function step2(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Parcours $parcours): Response
    {
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($parcours->getFormation()?->getTypeDiplome());
        $form = $this->createForm(ParcoursStep2Type::class, $parcours, [
            'typeDiplome' => $typeDiplome,
        ]);

        return $this->render('parcours_wizard/_step2.html.twig', [
            'form' => $form->createView(),
            'typeDiplome' => $typeDiplome,
            'parcours' => $parcours,
        ]);
    }

    #[Route('/{parcours}/3', name: 'app_parcours_wizard_step_3', methods: ['GET'])]
    public function step3(
        ParcoursRepository $parcoursRepository,
        Parcours $parcours): Response
    {
        $listeParcours = $parcoursRepository->findBy(['formation' => $parcours->getFormation()]);
        return $this->render('parcours_wizard/_step3.html.twig', [
            'parcours' => $parcours,
            'blocCompetences' => $parcours->getBlocCompetences(),
            'listeParcours' => $listeParcours,
        ]);
    }

    #[Route('/{parcours}/4', name: 'app_parcours_wizard_step_4', methods: ['GET'])]
    public function step4(Parcours $parcours): Response
    {
        return $this->render('parcours_wizard/_step4.html.twig', [
            'parcours' => $parcours,
        ]);
    }

    #[Route('/{parcours}/5', name: 'app_parcours_wizard_step_5', methods: ['GET'])]
    public function step5(Parcours $parcours): Response
    {
        $form = $this->createForm(ParcoursStep5Type::class, $parcours);

        return $this->render('parcours_wizard/_step5.html.twig', [
            'form' => $form->createView(),
            'parcours' => $parcours,
        ]);
    }

    #[Route('/{parcours}/6', name: 'app_parcours_wizard_step_6', methods: ['GET'])]
    public function step6(Parcours $parcours): Response
    {
        $form = $this->createForm(ParcoursStep6Type::class, $parcours);

        return $this->render('parcours_wizard/_step6.html.twig', [
            'form' => $form->createView(),
            'parcours' => $parcours,
        ]);
    }

    #[Route('/{parcours}/7', name: 'app_parcours_wizard_step_7', methods: ['GET'])]
    public function step7(Parcours $parcours): Response
    {
        $form = $this->createForm(ParcoursStep7Type::class, $parcours);

        return $this->render('parcours_wizard/_step7.html.twig', [
            'form' => $form->createView(),
            'parcours' => $parcours,
        ]);
    }

    #[Route('/{parcours}/code-rome', name: 'app_parcours_wizard_step_7_code_rome', methods: ['GET'])]
    public function codeRome(Parcours $parcours): Response
    {
        $codes = $parcours->getCodesRome();

        return $this->render('parcours_wizard/_codeRome.html.twig', [
            'parcours' => $parcours,
            'codes' => $codes,
        ]);
    }

    #[Route('/{parcours}/code-rome/gere', name: 'app_parcours_wizard_step_7_code_rome_gere', methods: ['POST'])]
    public function codeRomeGere(
        ParcoursRepository $parcoursRepository,
        Request $request,
        Parcours $parcours): Response
    {
        $action = JsonRequest::getValueFromRequest($request, 'action');
        $code = JsonRequest::getValueFromRequest($request, 'code');

        switch ($action) {
            case 'ADD':
                $codes = $parcours->getCodesRome();
                $codes[] = ['code' => $code];
                $parcours->setCodesRome($codes);

                $parcoursRepository->save($parcours, true);
                return $this->json(true);
            case 'DELETE':
                $codes = $parcours->getCodesRome();
                foreach ($codes as $key => $value) {
                    if ($value['code'] === $code) {
                        unset($codes[$key]);
                    }
                }
                $parcours->setCodesRome($codes);

                $parcoursRepository->save($parcours, true);
                return $this->json(true);
        }


        return $this->json(false);
    }

    #[Route('/{parcours}/8', name: 'app_parcours_wizard_step_8', methods: ['GET'])]
    public function step8(Parcours $parcours): Response
    {
        $form = $this->createForm(ParcoursStep8Type::class, $parcours);

        return $this->render('parcours_wizard/_step8.html.twig', [
            'form' => $form->createView(),
            'parcours' => $parcours,
        ]);
    }


}
