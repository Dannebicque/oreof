<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\EcOrdre;
use App\Classes\verif\FicheMatiereState;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\Ue;
use App\Form\EcStep4Type;
use App\Form\FicheMatiereType;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\LangueRepository;
use App\Repository\TypeEpreuveRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/fiche/matiere')]
class FicheMatiereController extends AbstractController
{
    public function __construct(private readonly WorkflowInterface $ecWorkflow)
    {
    }

    #[Route('/new/{ue}', name: 'app_fiche_matiere_new', methods: ['GET', 'POST'])]
    public function new(
        EntityManagerInterface $entityManager,
        LangueRepository $langueRepository,
        Request $request,
        Ue $ue
    ): Response {
        $ficheMatiere = new FicheMatiere();
        //todo: initialiser les modalités par rapport au parcours

        $form = $this->createForm(FicheMatiereType::class, $ficheMatiere, [
            'action' => $this->generateUrl('app_fiche_matiere_new', ['ue' => $ue->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ficheMatiere->setParcours($ue->getSemestre()?->getSemestreParcours()->first()->getParcours());

            $formation = $ue->getSemestre()?->getSemestreParcours()->first()->getParcours()?->getFormation();
            if ($formation === null) {
                throw new RuntimeException('Formation non trouvée');
            }


            $langueFr = $langueRepository->findOneBy(['codeIso' => 'fr']);
            if ($langueFr !== null) {
                $ficheMatiere->addLangueDispense($langueFr);
                $langueFr->addFicheMatiere($ficheMatiere);
                $ficheMatiere->addLangueSupport($langueFr);
                $langueFr->addLanguesSupportsFicheMatiere($ficheMatiere);
            }

            $entityManager->persist($ficheMatiere);
            $entityManager->flush();

            return $this->json(true);
        }

        return $this->render('fiche_matiere/new.html.twig', [
            'fiche_matiere' => $ficheMatiere,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}', name: 'app_fiche_matiere_show', methods: ['GET'])]
    public function show(
        FicheMatiere $ficheMatiere
    ): Response {
        $formation = $ficheMatiere->getParcours()->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }

        $bccs = [];
        foreach ($ficheMatiere->getCompetences() as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()?->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()?->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()?->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()?->getId()]['competences'][] = $competence;
        }

        $typeDiplome = $formation->getTypeDiplome();

        return $this->render('fiche_matiere/show.html.twig', [
            'ficheMatiere' => $ficheMatiere,
            'template' => $typeDiplome::TEMPLATE,//todo: revoir pour appeler le template global ?
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'bccs' => $bccs
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fiche_matiere_edit', methods: ['GET', 'POST'])]
    public function edit(
        FicheMatiere $ficheMatiere,
        FicheMatiereState $ficheMatiereState,
    ): Response {
        $ficheMatiereState->setFicheMatiere($ficheMatiere);
        //(is_granted('ROLE_FORMATION_EDIT_MY', ec.parcours.formation) or is_granted
        //                        ('ROLE_EC_EDIT_MY', ec)) and  workflow_can(ec,
        //                        'valider_ec')

//        $access = (($this->isGranted(
//            'ROLE_EC_EDIT_MY',
//            $ficheMatiere
//        ) && $this->ecWorkflow->can($ficheMatiere, 'valider_ec')) || ($this->isGranted(
//                'ROLE_FORMATION_EDIT_MY',
//                $parcours->getFormation()
//            )) || ($this->ecWorkflow->can($ficheMatiere, 'valider_ec') || $this->ecWorkflow->can($ficheMatiere, 'initialiser')) || $this->isGranted('ROLE_ADMIN'));
//
//        if (!$access) {
//            throw new AccessDeniedException();
//        }


        return $this->render('fiche_matiere/edit.html.twig', [
            'fiche_matiere' => $ficheMatiere,
            'ficheMatiereState' => $ficheMatiereState,
        ]);
    }

    #[Route('/{id}/dupliquer', name: 'app_fiche_matiere_dupliquer', methods: ['GET'])]
    public function dupliquer(
        FicheMatiere $ficheMatiere,
        ElementConstitutifRepository $elementConstitutifRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $newFicheMatiere = clone $ficheMatiere;
        $newFicheMatiere->setLibelle($ficheMatiere->getLibelle() . '-copie');
        $entityManager->persist($newFicheMatiere);
        $entityManager->flush();

        foreach ($ficheMatiere->getElementConstitutifs() as $elementConstitutif) {
            $newElementConstitutif = clone $elementConstitutif;
            $newElementConstitutif->setFicheMatiere($newFicheMatiere);

            if ($elementConstitutif->getSubOrdre() === 0) {
                $ordreMax = $elementConstitutifRepository->findLastEcSubOrdre(
                    $elementConstitutif->getUe(),
                    $elementConstitutif->getOrdre()
                );
                $newElementConstitutif->setOrdre($elementConstitutif->getOrdre());
                $newElementConstitutif->setSubOrdre($ordreMax + 1);
            } else {
                $ordreMax = $elementConstitutifRepository->findLastEc($elementConstitutif->getUe());
                $newElementConstitutif->setOrdre($ordreMax + 1);
            }
            $newElementConstitutif->genereCode();
            $entityManager->persist($newElementConstitutif);
            $entityManager->flush();
        }

        return $this->json(true);
    }

    #[Route('/{id}/structure-ec', name: 'app_fiche_matiere_structure', methods: ['GET', 'POST'])]
    public function structureEc(
        Request $request,
        FicheMatiereRepository $ficheMatiereRepository,
        FicheMatiere $ficheMatiere
    ): Response {
        if ($this->isGranted(
            'ROLE_FORMATION_EDIT_MY',
            $ficheMatiere->getParcours()->getFormation()
        )) { //todo: ajouter le workflow...
            $form = $this->createForm(EcStep4Type::class, $ficheMatiere, [
                'isModal' => true,
                'action' => $this->generateUrl(
                    'app_fiche_matiere_structure',
                    ['id' => $ficheMatiere->getId()]
                ),
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $ficheMatiereRepository->save($ficheMatiere, true);

                return $this->json(true);
            }

            return $this->render('fiche_matiere/_structureEcModal.html.twig', [
                'ec' => $ficheMatiere,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('fiche_matiere/_structureEcNonEditable.html.twig', [
            'ec' => $ficheMatiere,
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/mccc-ec', name: 'app_fiche_matiere_mccc', methods: ['GET', 'POST'])]
    public function mcccEc(
        TypeEpreuveRepository $typeEpreuveRepository,
        Request $request,
        FicheMatiereRepository $ficheMatiereRepository,
        FicheMatiere $ficheMatiere
    ): Response {
        $formation = $ficheMatiere->getParcours()->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $formation->getTypeDiplome();
        if ($this->isGranted(
            'ROLE_FORMATION_EDIT_MY',
            $ficheMatiere->getParcours()->getFormation()
        )) { //todo: ajouter le workflow...
            if ($ficheMatiere->getMcccs()->count() === 0) {
                $typeDiplome->initMcccs($ficheMatiere);//todo: appeler les mcc du bon diplôme
            }

            if ($request->isMethod('POST')) {
                $typeDiplome->saveMcccs($ficheMatiere, $request->request);//todo: appeler les mcc du bon diplôme
                $ficheMatiereRepository->save($ficheMatiere, true);

                return $this->json(true);
            }

            return $this->render('fiche_matiere/_mcccEcModal.html.twig', [
                'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
                'ec' => $ficheMatiere,
                'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC, //todo: appeler les mcc du bon diplôme
                'mcccs' => $typeDiplome->getMcccs($ficheMatiere), //todo: appeler les mcc du bon diplôme
                'wizard' => false
            ]);
        }

        return $this->render('fiche_matiere/_mcccEcNonEditable.html.twig', [
            'ec' => $ficheMatiere,
            'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
            'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC,
            'mcccs' => $typeDiplome->getMcccs($ficheMatiere),
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function displayMcccEc(
        TypeEpreuveRepository $typeEpreuveRepository,
        FicheMatiere $ficheMatiere
    ): Response {
        $formation = $ficheMatiere->getParcours()->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $formation->getTypeDiplome();

        return $this->render('fiche_matiere/_mcccEcNonEditable.html.twig', [
            'ec' => $ficheMatiere,
            'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
            'templateForm' => $typeDiplome::TEMPLATE_FORM_MCCC,//todo: appeler les mcc du bon diplôme
            'mcccs' => $typeDiplome->getMcccs($ficheMatiere),//todo: appeler les mcc du bon diplôme
        ]);
    }

    #[Route('/{id}/{ue}/deplacer/{sens}', name: 'app_fiche_matiere_deplacer', methods: ['GET'])]
    public function deplacer(
        EcOrdre $ecOrdre,
        FicheMatiere $ficheMatiere,
        Ue $ue,
        string $sens
    ): Response {
        $ecOrdre->deplacerFicheMatiere($ficheMatiere, $sens, $ue);

        return $this->json(true);
    }

    #[Route('/{id}', name: 'app_fiche_matiere_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        FicheMatiere $ficheMatiere,
        FicheMatiereRepository $ficheMatiereRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $ficheMatiere->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            $ficheMatiereRepository->remove($ficheMatiere, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
