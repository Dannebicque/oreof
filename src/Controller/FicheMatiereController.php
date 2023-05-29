<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\verif\FicheMatiereState;
use App\Entity\FicheMatiere;
use App\Form\FicheMatiereType;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\LangueRepository;
use App\Repository\UeRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/fiche/matiere')]
class FicheMatiereController extends AbstractController
{
    #[Route('/new', name: 'app_fiche_matiere_new', methods: ['GET', 'POST'])]
    public function new(
        UeRepository $ueRepository,
        EntityManagerInterface $entityManager,
        LangueRepository $langueRepository,
        Request $request,
    ): Response {
        $ficheMatiere = new FicheMatiere();
        if ($request->query->has('ue')) {
            $ue = $ueRepository->find($request->query->get('ue'));
            $ficheMatiere->setParcours($ue->getSemestre()?->getSemestreParcours()->first()->getParcours());
        }

        //todo: initialiser les modalités par rapport au parcours

        $form = $this->createForm(FicheMatiereType::class, $ficheMatiere, [
            'action' => $this->generateUrl('app_fiche_matiere_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'bccs' => $bccs
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fiche_matiere_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        FicheMatiere $ficheMatiere,
        FicheMatiereState $ficheMatiereState,
    ): Response {
        $ficheMatiereState->setFicheMatiere($ficheMatiere);

        $referer = $request->headers->get('referer');

        if ($referer === null || false === str_contains($referer, 'parcours')) {
            $source = 'liste';
        } else {
            $source = 'parcours';
            $link = $referer.'?step=4';
        }



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
            'source' => $source,
            'link' => $link ?? null,
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

            if ($elementConstitutif->getEcParent() !== null) {
                $ordreMax = $elementConstitutifRepository->findLastEcEnfant($elementConstitutif);
                $newElementConstitutif->setOrdre($ordreMax);
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

//    #[Route('/{id}/structure-ec', name: 'app_fiche_matiere_structure', methods: ['GET', 'POST'])]
//    public function structureEc(
//        Request $request,
//        FicheMatiereRepository $ficheMatiereRepository,
//        FicheMatiere $ficheMatiere
//    ): Response {
//        if ($this->isGranted(
//            'ROLE_FORMATION_EDIT_MY',
//            $ficheMatiere->getParcours()->getFormation()
//        )) { //todo: ajouter le workflow...
//            $form = $this->createForm(EcStep4Type::class, $ficheMatiere, [
//                'isModal' => true,
//                'action' => $this->generateUrl(
//                    'app_fiche_matiere_structure',
//                    ['id' => $ficheMatiere->getId()]
//                ),
//            ]);
//            $form->handleRequest($request);
//            if ($form->isSubmitted() && $form->isValid()) {
//                $ficheMatiereRepository->save($ficheMatiere, true);
//
//                return $this->json(true);
//            }
//
//            return $this->render('fiche_matiere/_structureEcModal.html.twig', [
//                'ec' => $ficheMatiere,
//                'form' => $form->createView(),
//            ]);
//        }
//
//        return $this->render('fiche_matiere/_structureEcNonEditable.html.twig', [
//            'ec' => $ficheMatiere,
//        ]);
//    }

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
