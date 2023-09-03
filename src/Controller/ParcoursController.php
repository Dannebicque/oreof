<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/ParcoursController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\CalculStructureParcours;
use App\Classes\verif\ParcoursState;
use App\DTO\HeuresEctsFormation;
use App\DTO\HeuresEctsSemestre;
use App\DTO\HeuresEctsUe;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\SemestreParcours;
use App\Events\AddCentreParcoursEvent;
use App\Form\ParcoursType;
use App\Repository\ElementConstitutifRepository;
use App\Repository\ParcoursRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Route('/parcours')]
class ParcoursController extends BaseController
{
    public function __construct(
        private WorkflowInterface $parcoursWorkflow
    ) {
    }

    #[Route('/', name: 'app_parcours_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('parcours/index.html.twig');
    }

    #[Route('/liste', name: 'app_parcours_liste', methods: ['GET'])]
    public function liste(
        ParcoursRepository $parcoursRepository,
        Request            $request
    ): Response {
        $sort = $request->query->get('sort') ?? 'libelle';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;


        $parcours = $parcoursRepository->findParcours(
            $this->getAnneeUniversitaire(),
            [$sort => $direction, 'recherche' => $q]
        );


        $tParcours = [];

        if ($this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_SES') ||
            $this->isGranted('CAN_PARCOURS_SHOW_ALL')) {
            $tParcours = $parcours;
        } else {
            foreach ($parcours as $p) {
                if ($this->isGranted('CAN_FORMATION_EDIT_MY', $p->getFormation()) ||
                    $this->isGranted('CAN_FORMATION_SHOW_MY', $p->getFormation()) ||
                    ($this->isGranted('CAN_PARCOURS_EDIT_MY', $p) && ($p->getRespParcours() === $this->getUser() || $p->getCoResponsable() === $this->getUser()))
                ) {
                    $tParcours[] = $p;
                }
            }
        }


        return $this->render('parcours/_liste.html.twig', [
            'parcours' => $tParcours,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    #[Route('/new/{formation}', name: 'app_parcours_new', methods: ['GET', 'POST'])]
    public function new(
        EventDispatcherInterface $eventDispatcher,
        Request                  $request,
        ParcoursRepository       $parcoursRepository,
        Formation                $formation
    ): Response {
//        if (!$this->isGranted('ROLE_RESP_FORMATION', $formation)) {
//            throw $this->createAccessDeniedException();
//        }


        $parent = null;
        $parcour = new Parcours($formation);
        if ($request->query->has('parent') && $request->query->get('parent') !== 'null') {
            $parent = $parcoursRepository->find($request->query->get('parent'));
            if (null === $parent) {
                throw $this->createNotFoundException();
            }
            $parcour->setParcoursParent($parent);
        }

        $parcour->setModalitesEnseignement(null);
        $form = $this->createForm(ParcoursType::class, $parcour, [
            'action' => $this->generateUrl('app_parcours_new', [
                'formation' => $formation->getId(),
                'parent' => $parent?->getId(),
            ]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->parcoursWorkflow->apply($parcour, 'initialiser');
            $parcoursRepository->save($parcour, true);

            $event = new AddCentreParcoursEvent($parcour, ['ROLE_RESP_PARCOURS'], $parcour->getRespParcours());
            $eventDispatcher->dispatch($event, AddCentreParcoursEvent::ADD_CENTRE_PARCOURS);

            if ($parcour->getCoResponsable() !== null) {
                $event = new AddCentreParcoursEvent($parcour, ['ROLE_CO_RESP_PARCOURS'], $parcour->getCoResponsable());
                $eventDispatcher->dispatch($event, AddCentreParcoursEvent::ADD_CENTRE_PARCOURS);
            }

            return $this->json(true);
        }

        return $this->render('parcours/new.html.twig', [
            'parcour' => $parcour,
            'form' => $form->createView(),
            'parent' => $parent,
            'texte' => $parent ? 'option' : 'parcours',
        ]);
    }

    #[Route('/edit/modal/{parcours}', name: 'app_parcours_edit_modal', methods: ['GET', 'POST'])]
    public function editModal(
        EventDispatcherInterface $eventDispatcher,
        Request                  $request,
        EntityManagerInterface   $entityManager,
        ParcoursRepository       $parcoursRepository,
        Parcours                 $parcours
    ): Response {
        $form = $this->createForm(ParcoursType::class, $parcours, [
            'action' => $this->generateUrl('app_parcours_edit_modal', [
                'parcours' => $parcours->getId(),
            ]),
        ]);
        $parent = $parcours->getParcoursParent();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uow = $entityManager->getUnitOfWork();
            $uow->computeChangeSets();
            $changeSet = $uow->getEntityChangeSet($parcours);

            if (isset($changeSet['respParcours'])) {
                // retirer l'ancien resp des centres et droits et envoyer mail
                $event = new AddCentreParcoursEvent($parcours, ['ROLE_RESP_PARCOURS'], $changeSet['respParcours'][0]);
                $eventDispatcher->dispatch($event, AddCentreParcoursEvent::REMOVE_CENTRE_PARCOURS);
                // ajouter le nouveau resp, ajouter centre et droits et envoyer mail
                $event = new AddCentreParcoursEvent($parcours, ['ROLE_RESP_PARCOURS'], $changeSet['respParcours'][1]);
                $eventDispatcher->dispatch($event, AddCentreParcoursEvent::ADD_CENTRE_PARCOURS);
            }

            $parcoursRepository->save($parcours, true);

            return $this->json(true);
        }

        return $this->render('parcours/new.html.twig', [
            'parcour' => $parcours,
            'form' => $form->createView(),
            'texte' => $parent ? 'option' : 'parcours',
        ]);
    }

    #[Route('/{id}', name: 'app_parcours_show', methods: ['GET'])]
    public function show(
        CalculStructureParcours $calculStructureParcours,
        Parcours            $parcours
    ): Response {
        $formation = $parcours->getFormation();
        if ($formation === null) {
            throw $this->createNotFoundException();
        }
        $typeDiplome = $formation->getTypeDiplome();

        $dto = $calculStructureParcours->calcul($parcours);

        return $this->render('parcours/show.html.twig', [
            'parcours' => $parcours,
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'hasParcours' => $formation->isHasParcours(),
            'dto' => $dto
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/edit', name: 'app_parcours_edit', methods: ['GET', 'POST'])]
    public function edit(
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Request             $request,
        ParcoursState       $parcoursState,
        Parcours            $parcour
    ): Response {
        $parcoursState->setParcours($parcour);
        $typeDiplome = $parcour->getFormation()?->getTypeDiplome();
        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());
        return $this->render('parcours/edit.html.twig', [
            'parcours' => $parcour,
            'typeDiplome' => $typeDiplome,
            'typeD' => $typeD,
            'formation' => $parcour->getFormation(),
            'parcoursState' => $parcoursState,
            'step' => $request->query->get('step') ?? 0,
        ]);
    }

    #[Route('/{id}/dupliquer', name: 'app_parcours_dupliquer', methods: ['GET'])]
    public function dupliquer(
        EntityManagerInterface $entityManager,
        Parcours               $parcour,
    ): Response {
        $formation = $parcour->getFormation();
        $newParcours = clone $parcour;
        $newParcours->setLibelle($parcour->getLibelle() . ' (copie)');
        $entityManager->persist($newParcours);

        //dupliquer les blocs et les compétences
        foreach ($parcour->getBlocCompetences() as $bloc) {
            $newBloc = clone $bloc;
            $newBloc->setParcours($newParcours);
            $entityManager->persist($newBloc);

            foreach ($bloc->getCompetences() as $competence) {
                $newCompetence = clone $competence;
                $newCompetence->setBlocCompetence($newBloc);
                $entityManager->persist($newCompetence);
            }
        }

        foreach ($parcour->getSemestreParcours() as $sp) {
            if ($sp->getSemestre()->isTroncCommun()) {
                //tronc commun, on duplique uniquement la liaison.
                $newSp = clone $sp;
                $newSp->setParcours($newParcours);
                $entityManager->persist($newSp);
            } else {
                //Pas tronc commun, on duplique semestre, UE et EC
                $newSemestre = clone $sp->getSemestre();
                $entityManager->persist($newSemestre);
                $newSp = new SemestreParcours($newSemestre, $newParcours);
                $entityManager->persist($newSp);

                foreach ($sp->getSemestre()->getUes() as $ue) {
                    $newUe = clone $ue;
                    $newUe->setSemestre($newSemestre);
                    $entityManager->persist($newUe);

                    //dupliquer les EC des ue
                    foreach ($ue->getElementConstitutifs() as $ec) {
                        //todo: on ne duplique pas les fiches EC/Matières ici ? Si on le fait, il faut reconstruire le lien vers les BC.
                        //todo: dupliquer les MCCC
                        $newEc = clone $ec;
                        $newEc->setUe($newUe);
                        $newEc->setParcours($newParcours);
                        $entityManager->persist($newEc);
                    }
                }
            }
        }

        $entityManager->flush();

        return $this->json(true);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_parcours_delete', methods: ['DELETE'])]
    public function delete(
        ElementConstitutifRepository $elementConstitutifRepository,
        EntityManagerInterface       $entityManager,
        Request                      $request,
        Parcours                     $parcour,
        ParcoursRepository           $parcoursRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $parcour->getId(), JsonRequest::getValueFromRequest($request, 'csrf'))) {
            foreach ($parcour->getSemestreParcours() as $sp) {
                $entityManager->remove($sp);
            }

            foreach ($parcour->getSemestreParcours() as $semestreParcour) {
                if ($semestreParcour->getSemestre()->isTroncCommun() === false) {
                    //todo: supprimer le tronc commun s'il n'est plus utilisé
                    foreach ($semestreParcour->getSemestre()->getUes() as $ue) {
                        foreach ($ue->getElementConstitutifs() as $ec) {
                            $entityManager->remove($ec);
                        }
                        $entityManager->remove($ue);
                    }
                    //todo: supprimer le semestre s'il n'est plus utilisé
                    $entityManager->remove($semestreParcour->getSemestre());
                    $entityManager->remove($semestreParcour);
                }

                foreach ($parcour->getFicheMatieres() as $ficheMatiere) {
                    //todo: gérer si la fiche est mutualisée
                    $entityManager->remove($ficheMatiere);
                }
            }

            $ecs = $elementConstitutifRepository->findByParcours($parcour);
            foreach ($ecs as $ec) {
                foreach ($ec->getEcEnfants() as $ece) {
                    $entityManager->remove($ece);
                }

                $entityManager->remove($ec);
            }

            foreach ($parcour->getFicheMatiereParcours() as $ficheMatiereParcour) {
                $entityManager->remove($ficheMatiereParcour);
            }

            foreach ($parcour->getSemestreMutualisables() as $semestreMutualisable) {
                $entityManager->remove($semestreMutualisable);
            }

            foreach ($parcour->getUeMutualisables() as $ueMutualisable) {
                $entityManager->remove($ueMutualisable);
            }

            $parcoursRepository->remove($parcour, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
