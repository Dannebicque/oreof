<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereWizardController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\verif\FicheMatiereState;
use App\Entity\FicheMatiere;
use App\Form\FicheMatiereStep1Type;
use App\Form\FicheMatiereStep2Type;
use App\Form\FicheMatiereStep3Type;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\Repository\SemestreRepository;
use App\Repository\UeRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/fiche-matiere/step')]
class FicheMatiereWizardController extends AbstractController
{
    #[Route('/', name: 'app_fiche_matiere_wizard', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('fiche_matiere/index.html.twig');
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/synthese', name: 'app_fiche_matiere_wizard_synthese', methods: ['GET'])]
    public function synthese(
        FicheMatiere $ficheMatiere,
    ): Response {

        return $this->render('fiche_matiere/_synthese_ec.html.twig', [
            'ficheMatiere' => $ficheMatiere,
        ]);
    }

    #[Route('/{ficheMatiere}/1', name: 'app_fiche_matiere_wizard_step_1', methods: ['GET'])]
    public function step1(FicheMatiere $ficheMatiere,): Response
    {
        $form = $this->createForm(FicheMatiereStep1Type::class, $ficheMatiere);

        return $this->render('fiche_matiere_wizard/_step1.html.twig', [
            'form' => $form->createView(),
            'ficheMatiere' => $ficheMatiere,
        ]);
    }

    #[Route('/{ficheMatiere}/mutualise', name: 'app_fiche_matiere_wizard_step_1_mutualise', methods: ['GET'])]
    public function mutualise(FicheMatiere $ficheMatiere): Response
    {
        $mutualises = [];

//        foreach ($ec->getEcUes() as $ue) {
//            foreach ($ue->getUe()->getSemestre()->getSemestreParcours() as $parc) {
//                $mutualises[] = [
//                    'parcours' => $parc->getParcours(),
//                    'semestre' => $ue->getUe()->getSemestre(),
//                    'ue' => $ue->getUe()
//                ];
//            }
//        }

        return $this->render('fiche_matiere_wizard/_step1_mutualise.html.twig', [
            'ficheMatiere' => $ficheMatiere,
            'mutualises' => $mutualises
        ]);
    }

    #[Route('/{ficheMatiere}/mutualise/add', name: 'app_fiche_matiere_wizard_step_1_mutualise_add', methods: ['GET'])]
    public function mutualiseAdd(
        ComposanteRepository $composanteRepository,
        FicheMatiere $ficheMatiere,
    ): Response
    {
        $composantes = $composanteRepository->findAll();

        return $this->render('fiche_matiere_wizard/_step1_mutualise_add.html.twig', [
            'fiche_matiere' => $ficheMatiere,
            'composantes' => $composantes
        ]);
    }

    #[Route('/{ficheMatiere}/mutualise/ajax', name: 'app_fiche_matiere_wizard_step_1_mutualise_add_ajax', methods: ['POST','DELETE'])]
    public function mutualiseAjax(
        EntityManagerInterface $entityManager,
        Request $request,
        FormationRepository $formationRepository,
        ParcoursRepository $parcoursRepository,
        ComposanteRepository $composanteRepository,
        SemestreRepository $semestreRepository,
        UeRepository $ueRepository,
        FicheMatiere $ficheMatiere,
    ): Response
    {
        $data = JsonRequest::getFromRequest($request);
        $t = [];
        switch($data['field']) {
            case 'formation':
                $formations = $formationRepository->findBy(['composantePorteuse' => $data['value']]);
                foreach ($formations as $formation) {
                    $t[] = [
                        'id' => $formation->getId(),
                        'libelle' => $formation->display()
                    ];
                }
                break;
            case 'parcours':
                $parcours = $parcoursRepository->findBy(['formation' => $data['value']]);
                foreach ($parcours as $parcour) {
                    $t[] = [
                        'id' => $parcour->getId(),
                        'libelle' => $parcour->getLibelle()
                    ];
                }
                break;
            case 'semestre':
                $parcours = $parcoursRepository->find($data['value']);
                $semestres = $parcours->getSemestreParcours();
                foreach ($semestres as $semestre) {
                    $t[] = [
                        'id' => $semestre->getId(),
                        'libelle' => $semestre->getSemestre()->display()
                    ];
                }
                break;
            case 'ue':
                $semestre = $semestreRepository->find($data['value']);
                $ues = $semestre->getUes();
                foreach ($ues as $ue) {
                    $t[] = [
                        'id' => $ue->getId(),
                        'libelle' => $ue->display()
                    ];
                }
                break;
            case 'save':
                $ue = $ueRepository->find($data['value']);
                $ecUe = new EcUe($ue, $ec);
                $entityManager->persist($ecUe);
                $ec->addEcUe($ecUe);
                $entityManager->flush();
                return $this->json(true);
            case 'delete':
                $ues = $ec->getEcUes();
                foreach ($ues as $ue) {
                    if ($ue !== null && $ue->getUe()->getId() === (int) $data['ue']) {
                        $ec->removeEcUe($ue);
                        $entityManager->remove($ue);
                    }
                }
                $entityManager->flush();
                return $this->json(true);
        }

        return $this->json($t);
    }

    #[Route('/{ficheMatiere}/2', name: 'app_fiche_matiere_wizard_step_2', methods: ['GET'])]
    public function step2(
        FicheMatiere $ficheMatiere,
    ): Response
    {
        $form = $this->createForm(FicheMatiereStep2Type::class, $ficheMatiere);


        return $this->render('fiche_matiere_wizard/_step2.html.twig', [
            'form' => $form->createView(),
            'ficheMatiere' => $ficheMatiere,
        ]);
    }

    #[Route('/{ficheMatiere}/3', name: 'app_fiche_matiere_wizard_step_3', methods: ['GET'])]
    public function step3(
        FicheMatiere $ficheMatiere,
    ): Response {
        $form = $this->createForm(FicheMatiereStep3Type::class, $ficheMatiere);

        $ecBccs = [];
        $ecComps = [];

        foreach ($ficheMatiere->getCompetences() as $competence) {
            $ecComps[] = $competence->getId();
            $ecBccs[] = $competence->getBlocCompetence()?->getId();
        }

        return $this->render('fiche_matiere_wizard/_step3.html.twig', [
            'ficheMatiere' => $ficheMatiere,
            'form' => $form->createView(),
            'bccs' => $ficheMatiere->getParcours()?->getBlocCompetences(),
            'ecBccs' => array_flip(array_unique($ecBccs)),
            'ecComps' => array_flip($ecComps),
        ]);
    }
}
