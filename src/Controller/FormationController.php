<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FormationController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\verif\FormationState;
use App\Classes\verif\ParcoursState;
use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\FormationDemande;
use App\Entity\UserCentre;
use App\Events\AddCentreFormationEvent;
use App\Form\FormationDemandeType;
use App\Form\FormationSesType;
use App\Repository\ComposanteRepository;
use App\Repository\DomaineRepository;
use App\Repository\FormationRepository;
use App\Repository\MentionRepository;
use App\Repository\RoleRepository;
use App\Repository\TypeDiplomeRepository;
use App\Repository\UserCentreRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/formation')]
class FormationController extends BaseController
{
    #[Route('/', name: 'app_formation_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('formation/index.html.twig');
    }

    #[Route('/liste', name: 'app_formation_liste', methods: ['GET'])]
    public function liste(
        ComposanteRepository  $composanteRepository,
        TypeDiplomeRepository $typeDiplomeRepository,
        FormationRepository   $formationRepository,
        Request               $request
    ): Response {
        $sort = $request->query->get('sort') ?? 'typeDiplome';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;

        if ($this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_SES') ||
            $this->isGranted('CAN_COMPOSANTE_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('CAN_FORMATION_SHOW_ALL', $this->getUser())) {
            $formations = $formationRepository->findBySearch($q, $this->getAnneeUniversitaire(), $request->query->all());
        } else {
            $formations = [];

            //gérer le cas ou l'utilisateur dispose des droits pour lire la composante
            $centres = $this->getUser()->getUserCentres();
            foreach ($centres as $centre) {
                if ($centre->getComposante() !== null && in_array('Gestionnaire', $centre->getDroits())) {
                    //todo: il faudrait pouvoir filtrer par ce que contient le rôle et pas juste le nom
                    $formations[] = $formationRepository->findByComposante(
                        $centre->getComposante(),
                        $this->getAnneeUniversitaire(),
                        [$sort => $direction]
                    );
                }
            }


            $formations[] = $formationRepository->findByComposanteDpe(
                $this->getUser(),
                $this->getAnneeUniversitaire(),
                [$sort => $direction]
            );
            $formations[] = $formationRepository->findByResponsableOuCoResponsable(
                $this->getUser(),
                $this->getAnneeUniversitaire(),
                [$sort => $direction]
            );
            $formations[] = $formationRepository->findByResponsableOuCoResponsableParcours(
                $this->getUser(),
                $this->getAnneeUniversitaire(),
                [$sort => $direction]
            );
            $formations = array_merge(...$formations);
        }

        $tFormations = [];
        foreach ($formations as $formation) {
            $tFormations[$formation->getId()] = $formation;
        }

        return $this->render('formation/_liste.html.twig', [
            'formations' => $tFormations,
            'composantes' => $composanteRepository->findBy([], ['libelle' => 'ASC']),
            'typeDiplomes' => $typeDiplomeRepository->findBy([], ['libelle' => 'ASC']),
            'params' => $request->query->all()
        ]);
    }

    #[Route('/liste/{composante}', name: 'app_formation_liste_composante', methods: ['GET'])]
    public function listeComposante(
        FormationRepository $formationRepository,
        Composante          $composante,
        Request             $request
    ): Response {
        $sort = $request->query->get('sort') ?? 'typeDiplome';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;


        if ($q) {
            $formations = $formationRepository->findBySearch(
                $q,
                $this->getAnneeUniversitaire(),
                $sort,
                $direction,
                $composante
            );
        } else {
            $formations = $formationRepository->findBy(
                ['composantePorteuse' => $composante->getId(), 'anneeUniversitaire' => $this->getAnneeUniversitaire()],
                [$sort => $direction]
            );
        }


        return $this->render('formation/_liste.html.twig', [
            'formations' => $formations,
            'sort' => $sort,
            'direction' => $direction
        ]);
    }

    #[Route('/demande', name: 'app_formation_demande_new', methods: ['GET', 'POST'])]
    public function demande(
        RoleRepository       $roleRepository,
        MentionRepository    $mentionRepository,
        UserCentreRepository $userCentreRepository,
        Request              $request,
        FormationRepository  $formationRepository
    ): Response {
        $formationDemande = new FormationDemande();
        $form = $this->createForm(FormationDemandeType::class, $formationDemande, [
            'action' => $this->generateUrl('app_formation_demande_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (array_key_exists(
                'mention',
                $request->request->all()['formation_ses']
            ) && $request->request->all()['formation_ses']['mention'] !== null && $request->request->all()['formation_ses']['mention'] !== 'autre') {
                $mention = $mentionRepository->find($request->request->all()['formation_ses']['mention']);
                $formation->setMentionTexte(null);
                $formation->setMention($mention);
            }

            $formation->addComposantesInscription($formation->getComposantePorteuse());
            $formationRepository->save($formation, true);

            //on vérifie si le responsable de formation à le centre
            $hasCentre = $userCentreRepository->findOneBy([
                'user' => $formation->getResponsableMention(),
                'formation' => $formation->getId()
            ]);
            if ($hasCentre === null) {
                $role = $roleRepository->findOneBy(['code_role' => 'ROLE_RESP_FORMATION']);
                $uc = new UserCentre();
                $uc->setUser($formation->getResponsableMention());
                $uc->setFormation($formation);
                $uc->addRole($role);
                $userCentreRepository->save($uc, true);
            }

            return $this->redirectToRoute('app_formation_index');
        }

        return $this->render('formation/new.html.twig', [
            'formationDemande' => $formationDemande,
            'form' => $form->createView()
        ]);
    }

    #[Route('/new', name: 'app_formation_new', methods: ['GET', 'POST'])]
    public function new(
        RoleRepository       $roleRepository,
        MentionRepository    $mentionRepository,
        UserCentreRepository $userCentreRepository,
        Request              $request,
        FormationRepository  $formationRepository
    ): Response {
        $this->denyAccessUnlessGranted('CAN_FORMATION_CREATE_ALL', $this->getUser());

        $formation = new Formation($this->getAnneeUniversitaire());
        $form = $this->createForm(FormationSesType::class, $formation, [
            'action' => $this->generateUrl('app_formation_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (array_key_exists(
                'mention',
                $request->request->all()['formation_ses']
            ) && $request->request->all()['formation_ses']['mention'] !== null && $request->request->all()['formation_ses']['mention'] !== 'autre') {
                $mention = $mentionRepository->find($request->request->all()['formation_ses']['mention']);
                $formation->setMentionTexte(null);
                $formation->setMention($mention);
            }

            $formation->addComposantesInscription($formation->getComposantePorteuse());
            $formationRepository->save($formation, true);

            //on vérifie si le responsable de formation à le centre
            $hasCentre = $userCentreRepository->findOneBy([
                'user' => $formation->getResponsableMention(),
                'formation' => $formation->getId()
            ]);
            if ($hasCentre === null) {
                $role = $roleRepository->findOneBy(['code_role' => 'ROLE_RESP_FORMATION']);
                $uc = new UserCentre();
                $uc->setUser($formation->getResponsableMention());
                $uc->setFormation($formation);
                $uc->addRole($role);
                $userCentreRepository->save($uc, true);
            }

            return $this->redirectToRoute('app_formation_index');
        }

        return $this->render('formation/new.html.twig', [
            'formation' => $formation,
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/formation/{formation}', name: 'app_formation_edit_modal', methods: ['GET', 'POST'])]
    public function editModal(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface   $entityManager,
        MentionRepository        $mentionRepository,
        Request                  $request,
        FormationRepository      $formationRepository,
        Formation                $formation
    ): Response {
        $form = $this->createForm(FormationSesType::class, $formation, [
            'action' => $this->generateUrl('app_formation_edit_modal', ['formation' => $formation->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {//todo: si validate le choice de mention ne fonctionne pas
            if (array_key_exists(
                'mention',
                $request->request->all()['formation_ses']
            ) && $request->request->all()['formation_ses']['mention'] !== null && $request->request->all()['formation_ses']['mention'] !== 'autre') {
                $mention = $mentionRepository->find($request->request->all()['formation_ses']['mention']);
                $formation->setMentionTexte(null);
                $formation->setMention($mention);
            }

            $uow = $entityManager->getUnitOfWork();
            $uow->computeChangeSets();
            $changeSet = $uow->getEntityChangeSet($formation);

            if (isset($changeSet['responsableMention'])) {
                // retirer l'ancien resp des centres et droits et envoyer mail
                $event = new AddCentreFormationEvent($formation, ['ROLE_RESP_FORMATION'], $changeSet['responsableMention'][0]);
                $eventDispatcher->dispatch($event, AddCentreFormationEvent::REMOVE_CENTRE_FORMATION);
                // ajouter le nouveau resp, ajouter centre et droits et envoyer mail
                $event = new AddCentreFormationEvent($formation, ['ROLE_RESP_FORMATION'], $changeSet['responsableMention'][1]);
                $eventDispatcher->dispatch($event, AddCentreFormationEvent::ADD_CENTRE_FORMATION);
            }

            $formationRepository->save($formation, true);

            return $this->redirectToRoute('app_formation_edit', ['id' => $formation->getId()]);
        }

        return $this->render('formation/editModal.html.twig', [
            'formation' => $formation,
            'form' => $form->createView()
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/api', name: 'app_formation_api', methods: ['GET'])]
    public function api(
        MentionRepository     $mentionRepository,
        TypeDiplomeRepository $typeDiplomeRepository,
        DomaineRepository     $domaineRepository,
        Request               $request
    ): Response {
        $domaine = $domaineRepository->find($request->query->get('domaine'));

        if ($domaine === null) {
            return $this->json([
                'mentions' => [],
            ]);
        }

        $typeDiplome = $typeDiplomeRepository->find($request->query->get('typeDiplome'));

        return $this->json([
            'mentions' => $mentionRepository->findByDomaineAndTypeDiplomeArray($domaine, $typeDiplome),
            'selectedMention' => $request->query->get('mention')
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}', name: 'app_formation_show', methods: ['GET'])]
    public function show(
        Formation $formation
    ): Response {
        $typeDiplome = $formation->getTypeDiplome();

        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/edit', name: 'app_formation_edit', methods: ['GET', 'POST'])]
    public function edit(
        ParcoursState       $parcoursState,
        FormationState      $formationState,
        Request             $request,
        Formation           $formation,
        TypeDiplomeRegistry $typeDiplomeRegistry
    ): Response {
        //todo: tester les droits et si on est en place "en_cours_redaction" => voter
        $formationState->setFormation($formation);
        $typeD = $typeDiplomeRegistry->getTypeDiplome($formation->getTypeDiplome()->getModeleMcc());
        if ($formation->getParcours()?->first() !== false) {
            $parcoursState->setParcours($formation->getParcours()?->first());
        }

        return $this->render('formation/edit.html.twig', [
            'formation' => $formation,
            'selectedStep' => $request->query->get('step', 1),
            'typeDiplome' => $formation->getTypeDiplome(),
            'parcoursState' => $parcoursState,
            'formationState' => $formationState,
            'typeD' => $typeD
        ]);
    }

    #[Route('/{id}', name: 'app_formation_delete', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Request                $request,
        Formation              $formation,
        FormationRepository    $formationRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $formation->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            foreach ($formation->getParcours() as $parcours) {
                foreach ($parcours->getBlocCompetences() as $blocs) {
                    $entityManager->remove($blocs);
                }

                foreach ($parcours->getSemestreParcours() as $semestreParcour) {
                    $semestre = $semestreParcour->getSemestre();
                    if ($semestre !== null) {
                        foreach ($semestre->getUes() as $ue) {
                            foreach ($ue->getElementConstitutifs() as $ec) {
                                $entityManager->remove($ec);
                            }
                            if ($semestre->isTroncCommun() === false) {
                                $entityManager->remove($ue);
                            }
                        }
                        if ($semestre->isTroncCommun() === false) {
                            $entityManager->remove($semestre);
                        }
                    }

                    $entityManager->remove($semestreParcour);
                }

                foreach ($parcours->getFicheMatiereParcours() as $fiche) {
                    $entityManager->remove($fiche);
                }
                $entityManager->flush();
                $entityManager->remove($parcours);
            }
            foreach ($formation->getBlocCompetences() as $blocs) {
                $entityManager->remove($blocs);
            }
            $formationRepository->remove($formation, true);

            return $this->json(true);
        }

        return $this->json(false);
    }
}
