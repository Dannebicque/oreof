<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FicheMatiereController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\JsonReponse;
use App\Classes\verif\FicheMatiereState;
use App\Entity\ElementConstitutif;
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
        } else {
            $ficheMatiere->setHorsDiplome(true);
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
    #[Route('/{slug}', name: 'app_fiche_matiere_show', methods: ['GET'])]
    public function show(
        FicheMatiere $ficheMatiere
    ): Response {
        $formation = $ficheMatiere->getParcours()?->getFormation();
//        if ($formation === null) {
//            throw new RuntimeException('Formation non trouvée');
//        }

        $bccs = [];
        foreach ($ficheMatiere->getCompetences() as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()?->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()?->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()?->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()?->getId()]['competences'][] = $competence;
        }

        if ($formation !== null) {
            $typeDiplome = $formation->getTypeDiplome();
        } else {
            $typeDiplome = null;
        }

        return $this->render('fiche_matiere/show.html.twig', [
            'ficheMatiere' => $ficheMatiere,
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'bccs' => $bccs
        ]);
    }

    #[Route('/{elementConstitutif}/show-parcours', name: 'app_fiche_matiere_detail_parcours', methods: ['GET'])]
    public function showParcours(
        ElementConstitutif $elementConstitutif
    ): Response {

        if ($elementConstitutif->isFicheFromParcours() === true) {
            $competences = $elementConstitutif->getFicheMatiere()->getCompetences();
        } else {
            $competences = $elementConstitutif->getCompetences();
        }

        $bccs = [];
        foreach ($competences as $competence) {
            if (!array_key_exists($competence->getBlocCompetence()?->getId(), $bccs)) {
                $bccs[$competence->getBlocCompetence()?->getId()]['bcc'] = $competence->getBlocCompetence();
                $bccs[$competence->getBlocCompetence()?->getId()]['competences'] = [];
            }
            $bccs[$competence->getBlocCompetence()?->getId()]['competences'][] = $competence;
        }


        return $this->render('fiche_matiere/_showParcours.html.twig', [
            'ficheMatiere' => $elementConstitutif->getFicheMatiere(),
            'bccs' => $bccs
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_fiche_matiere_edit', methods: ['GET', 'POST'])]
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

        return $this->render('fiche_matiere/edit.html.twig', [
            'fiche_matiere' => $ficheMatiere,
            'ficheMatiereState' => $ficheMatiereState,
            'source' => $source,
            'link' => $link ?? null,
        ]);
    }

    #[Route('/{slug}/dupliquer', name: 'app_fiche_matiere_dupliquer', methods: ['GET'])]
    public function dupliquer(
        FicheMatiere $ficheMatiere,
        ElementConstitutifRepository $elementConstitutifRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $newFicheMatiere = clone $ficheMatiere;
        $newFicheMatiere->setLibelle($ficheMatiere->getLibelle() . '-copie');
        $newFicheMatiere->setSlug(null);
        $entityManager->persist($newFicheMatiere);
        $entityManager->flush();

        foreach($ficheMatiere->getFicheMatiereParcours() as $parcours) {
            //on duplique les parcours de mutualisation
            $newFicheMatiereParcours = clone $parcours;
            $newFicheMatiereParcours->setFicheMatiere($newFicheMatiere);
            $entityManager->persist($newFicheMatiereParcours);
            $entityManager->flush();
        }

        return $this->json(true);
    }

    #[Route('/{slug}', name: 'app_fiche_matiere_delete', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Request $request,
        FicheMatiere $ficheMatiere,
        FicheMatiereRepository $ficheMatiereRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $ficheMatiere->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {

            if ($ficheMatiere->getElementConstitutifs()->count() > 0) {
                return JsonReponse::error('Impossible de supprimer la fiche matière car elle est utilisée par au moins un élément constitutif.');
            }

            if ($ficheMatiere->getFicheMatiereParcours()->count() > 0) {
                return JsonReponse::error('Impossible de supprimer la fiche matière car elle est potentiellement mutualisée avec d\'autres parcours.');
            }

            foreach ($ficheMatiere->getMcccs() as $mccc) {
                $ficheMatiere->removeMccc($mccc);
                $entityManager->remove($mccc);
            }

            $ficheMatiereRepository->remove($ficheMatiere, true);

            return JsonReponse::success('La fiche matière a bien été supprimée.');
        }

        return $this->json(false);
    }
}
