<?php

namespace App\Controller;

use App\Entity\ElementConstitutif;
use App\Entity\Parcours;
use App\Form\EcStep1Type;
use App\Form\EcStep2Type;
use App\Form\EcStep3Type;
use App\Form\EcStep4Type;
use App\Repository\ElementConstitutifRepository;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ec/step')]
class ElementConstitutifWizardController extends AbstractController
{
    #[Route('/', name: 'app_ec_wizard', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('element_constitutif/index.html.twig');
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/{parcours}/synthese', name: 'app_ec_wizard_synthese', methods: ['GET'])]
    public function synthese(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        ElementConstitutif $elementConstitutif,
        Parcours $parcours
    ): Response
    {
        $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($parcours->getFormation()?->getTypeDiplome());

        return $this->render('element_constitutif/_synthese_ec.html.twig', [
            'ec' => $elementConstitutif,
            'parcours' => $parcours,
            'typeDiplome' => $typeDiplome,
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

        $ecBccs = [];
        $ecComps = [];

        foreach ($ec->getCompetences() as $competence) {
            $ecComps[] = $competence->getId();
            $ecBccs[] = $competence->getBlocCompetence()?->getId();
        }

        return $this->render('element_constitutif_wizard/_step3.html.twig', [
            'ec' => $ec,
            'parcours' => $parcours,
            'form' => $form->createView(),
            'bccs' => $parcours->getBlocCompetences(),
            'ecBccs' => array_flip(array_unique($ecBccs)),
            'ecComps' => array_flip($ecComps),
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

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{ec}/{parcours}/5', name: 'app_ec_wizard_step_5', methods: ['GET'])]
    public function step5(
        TypeEpreuveRepository $typeEpreuveRepository,
        TypeDiplomeRegistry $typeDiplomeRegistry,
        ElementConstitutif $ec, Parcours $parcours
    ): Response {

        $formation = $ec->getParcours()->getFormation();
        if ($formation === null) {
            throw new Exception('Formation non trouvée');
        }
            $typeDiplome = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome());
            if ($this->isGranted('ROLE_FORMATION_EDIT_MY',
                $ec->getParcours()->getFormation())) { //todo: ajouter le workflow...


                if ($ec->getMcccs()->count() === 0) {
                    $typeDiplome->initMcccs($ec);
                }

                return $this->render('element_constitutif_wizard/_step5.html.twig', [
                    'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
                    'ec' => $ec,
                    'parcours' => $parcours,
                    'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC,
                    'mcccs' => $typeDiplome->getMcccs($ec),
                    'editable' => true,
                    'wizard' => true
                ]);

            }

            return $this->render('element_constitutif_wizard/_step5.html.twig', [
                'ec' => $ec,
                'parcours' => $parcours,
                'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
                'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC,
                'mcccs' => $typeDiplome->getMcccs($ec),
                'editable' => false,
            ]);
    }
}
