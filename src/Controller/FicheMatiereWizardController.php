<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereWizardController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\GetDpeParcours;
use App\Entity\FicheMatiere;
use App\Entity\FicheMatiereMutualisable;
use App\Form\FicheMatiereStep1Type;
use App\Form\FicheMatiereStep2Type;
use App\Form\FicheMatiereStep3Type;
use App\Form\FicheMatiereStep4HdType;
use App\Form\FicheMatiereStep4Type;
use App\Repository\BlocCompetenceRepository;
use App\Repository\ButCompetenceRepository;
use App\Repository\ComposanteRepository;
use App\Repository\FicheMatiereMutualisableRepository;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\Access;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    public function step1(FicheMatiere $ficheMatiere): Response
    {
        if ($ficheMatiere->getParcours() !== null && $ficheMatiere->getParcours()->getFormation()?->getTypeDiplome()?->getLibelleCourt() === 'BUT') {
            $isBut = true;
        } else {
            $isBut = false;
        }
        $isScol = $this->isGranted('CAN_ETABLISSEMENT_SCOLARITE_ALL', $this->getuser());
        $form = $this->createForm(FicheMatiereStep1Type::class, $ficheMatiere, [
            'isBut' => $isBut,
            'isScol' => $isScol,
        ]);
        return $this->render('fiche_matiere_wizard/_step1.html.twig', [
            'form' => $form->createView(),
            'ficheMatiere' => $ficheMatiere,
            'isBut' => $isBut,
            'isScol' => $isScol,

        ]);
    }

    #[Route('/{ficheMatiere}/mutualise', name: 'app_fiche_matiere_wizard_step_1_mutualise', methods: ['GET'])]
    public function mutualise(FicheMatiere $ficheMatiere): Response
    {
        return $this->render('fiche_matiere_wizard/_step1_mutualise.html.twig', [
            'ficheMatiere' => $ficheMatiere,
        ]);
    }

    #[Route('/{ficheMatiere}/mutualise/add', name: 'app_fiche_matiere_wizard_step_1_mutualise_add', methods: ['GET'])]
    public function mutualiseAdd(
        ComposanteRepository $composanteRepository,
        FicheMatiere         $ficheMatiere,
    ): Response {
        $composantes = $composanteRepository->findAll();//todo: filtrer dans les formations

        return $this->render('fiche_matiere_wizard/_step1_mutualise_add.html.twig', [
            'ficheMatiere' => $ficheMatiere,
            'composantes' => $composantes
        ]);
    }

    #[Route('/{ficheMatiere}/mutualise/ajax', name: 'app_fiche_matiere_wizard_step_1_mutualise_add_ajax', methods: [
        'POST',
        'DELETE'
    ])]
    public function mutualiseAjax(
        EntityManagerInterface             $entityManager,
        ComposanteRepository               $composanteRepository,
        Request                            $request,
        FormationRepository                $formationRepository,
        ParcoursRepository                 $parcoursRepository,
        FicheMatiereMutualisableRepository $ficheMatiereParcoursRepository,
        FicheMatiere                       $ficheMatiere,
    ): Response {
        $data = JsonRequest::getFromRequest($request);
        $t = [];
        switch ($data['field']) {
            case 'formation':
                $formations = $formationRepository->findBy(['composantePorteuse' => $data['value']]);
                foreach ($formations as $formation) {
                    $t[] = [
                        'id' => $formation->getId(),
                        'libelle' => $formation->getDisplayLong()
                    ];
                }
                break;
            case 'parcours':
                $parcours = $parcoursRepository->findBy(['formation' => $data['value']]);
                foreach ($parcours as $parcour) {
                    $t[] = [
                        'id' => $parcour->getId(),
                        'libelle' => $parcour->getDisplay()
                    ];
                }
                break;
            case 'save':

                if ($data['formation'] === 'all') {
                    $composante = $composanteRepository->find($data['composante']);
                    if ($composante !== null and !$ficheMatiere->getComposante()->contains($composante)) {
                        $ficheMatiere->addComposante($composante);
                        $composante->addFicheMatiere($ficheMatiere);
                        $entityManager->flush();
                    }
                    return $this->json(true);
                }

                if (!isset($data['parcours']) || $data['parcours'] === '') {
                    $formation = $formationRepository->find($data['formation']);
                    if ($formation !== null && $formation->isHasParcours() === false && count($formation->getParcours()) === 1) {
                        $parcours = $formation->getParcours()[0];
                        $this->updateFicheMatiereMutualisable($ficheMatiereParcoursRepository, $ficheMatiere, $parcours, $entityManager);
                    }
                } else {
                    if ($data['parcours'] !== 'all') {
                        $parcours = $parcoursRepository->find($data['parcours']);
                        $this->updateFicheMatiereMutualisable($ficheMatiereParcoursRepository, $ficheMatiere, $parcours, $entityManager);
                    } else {
                        $formation = $formationRepository->find($data['formation']);
                        if ($formation !== null) {
                            foreach ($formation->getParcours() as $parcours) {
                                $this->updateFicheMatiereMutualisable($ficheMatiereParcoursRepository, $ficheMatiere, $parcours, $entityManager);
                            }
                        }
                    }
                }


                return $this->json(true);
            case 'delete':
                $fiche = $ficheMatiereParcoursRepository->find($data['fiche']);
                if ($fiche !== null) {
                    $entityManager->remove($fiche);
                    $entityManager->flush();
                }
                //todo: supprimer les EC qui utilisent ?

                return $this->json(true);
        }

        return $this->json($t);
    }

    #[Route('/{ficheMatiere}/2', name: 'app_fiche_matiere_wizard_step_2', methods: ['GET'])]
    public function step2(
        FicheMatiere $ficheMatiere,
    ): Response {
        $form = $this->createForm(FicheMatiereStep2Type::class, $ficheMatiere);


        return $this->render('fiche_matiere_wizard/_step2.html.twig', [
            'form' => $form->createView(),
            'ficheMatiere' => $ficheMatiere,
        ]);
    }

    #[Route('/{ficheMatiere}/3', name: 'app_fiche_matiere_wizard_step_3', methods: ['GET'])]
    public function step3(
        ButCompetenceRepository  $butCompetenceRepository,
        BlocCompetenceRepository $blocCompetenceRepository,
        FicheMatiere             $ficheMatiere,
    ): Response {
        $form = $this->createForm(FicheMatiereStep3Type::class, $ficheMatiere);
        $isBut = false;
        if ($ficheMatiere->getParcours() !== null && $ficheMatiere->getParcours()->getFormation()?->getTypeDiplome()?->getLibelleCourt() === 'BUT') {
            $isBut = true;
            $ecBccs = [];
            $ecComps = [];

            foreach ($ficheMatiere->getApprentissagesCritiques() as $competence) {
                $ecComps[] = $competence->getId();
                $ecBccs[] = $competence->getNiveau()?->getCompetence()?->getId();
            }

            $bccs = $butCompetenceRepository->findBy(['formation' => $ficheMatiere->getParcours()->getFormation()], ['numero' => 'ASC']);
        } else {
            $ecBccs = [];
            $ecComps = [];

            foreach ($ficheMatiere->getCompetences() as $competence) {
                $ecComps[] = $competence->getId();
                $ecBccs[] = $competence->getBlocCompetence()?->getId();
            }


            if ($ficheMatiere->getParcours() !== null) {
                $bccs = $blocCompetenceRepository->findByParcours($ficheMatiere->getParcours());
            } else {
                $bccs = $blocCompetenceRepository->findBy(['parcours' => null]);
            }
        }


        return $this->render('fiche_matiere_wizard/_step3.html.twig', [
            'ficheMatiere' => $ficheMatiere,
            'form' => $form->createView(),
            'bccs' => $bccs,
            'isBut' => $isBut,
            'ecBccs' => array_flip(array_unique($ecBccs)),
            'ecComps' => array_flip($ecComps),
        ]);
    }

    #[Route('/{ficheMatiere}/4/{type}', name: 'app_fiche_matiere_wizard_step_4', methods: ['GET'])]
    public function step4(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        TypeEpreuveRepository        $typeEpreuveRepository,

        FicheMatiere        $ficheMatiere,
        string              $type,
    ): Response {
        if ($ficheMatiere->getParcours() !== null) {
            $dpeParcours = GetDpeParcours::getFromParcours($ficheMatiere->getParcours());
        }

        $parcours = [];
        $ecProprietaire = null;
        foreach ($ficheMatiere->getElementConstitutifs() as $ec) {
            if ($ec->getParcours() !== null) {
                $parcours[] = $ec->getParcours();
            }

            if ($ec->getParcours() === $ficheMatiere->getParcours()) {
                $ecProprietaire = $ec;
            }
        }

        $typeDiplome = $ficheMatiere->getParcours()?->getFormation()?->getTypeDiplome();

        if ($type === 'but') {
//            dump($dpeParcours);
//            if ($dpeParcours !==null && !Access::isAccessible($dpeParcours, 'cfvu')) {
//                return $this->render('fiche_matiere_wizard/_access_denied.html.twig');
//            }
            $form = $this->createForm(FicheMatiereStep4Type::class, $ficheMatiere);
            $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

            return $this->render('fiche_matiere_wizard/_step4But.html.twig', [
                'mcccs' => $typeD->getMcccs($ficheMatiere),
                'ficheMatiere' => $ficheMatiere,
                'form' => $form->createView(),
            ]);
        }

        if ($type === 'hd') {
            $form = $this->createForm(FicheMatiereStep4HdType::class, $ficheMatiere);

            return $this->render('fiche_matiere_wizard/_step4Hd.html.twig', [
                'ficheMatiere' => $ficheMatiere,
                'form' => $form->createView(),
            ]);
        }

        if ($type === 'other') {
            $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());
            // $form = $this->createForm(FicheMatiereStep4HdType::class, $ficheMatiere);
            return $this->render('fiche_matiere_wizard/_step4Other.html.twig', [
                'ficheMatiere' => $ficheMatiere,
               'parcours' => $parcours,
                'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),

                'mcccs' => $typeD !== null ? $typeD->getMcccs($ficheMatiere) : [],
                'ecProprietaire' => $ecProprietaire,
                'typeMccc' => $ficheMatiere->getTypeMccc(),
                'templateForm' => $typeD !== null ? $typeD::TEMPLATE_FORM_MCCC : '',
               // 'form' => $form->createView(),
            ]);
        }
    }

    private function updateFicheMatiereMutualisable(
        FicheMatiereMutualisableRepository $ficheMatiereParcoursRepository,
        FicheMatiere                       $ficheMatiere,
        mixed                              $parcours,
        EntityManagerInterface             $entityManager
    ): void {
        $exist = $ficheMatiereParcoursRepository->findOneBy([
            'ficheMatiere' => $ficheMatiere,
            'parcours' => $parcours
        ]);

        if ($exist === null) {
            $ficheMatiereParcours = new FicheMatiereMutualisable();
            $ficheMatiereParcours->setFicheMatiere($ficheMatiere);
            $ficheMatiereParcours->setParcours($parcours);
            $entityManager->persist($ficheMatiereParcours);
            $entityManager->flush();
        }
    }
}
