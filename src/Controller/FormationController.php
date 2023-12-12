<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/FormationController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller;

use App\Classes\CalculStructureParcours;
use App\Classes\verif\FormationState;
use App\Classes\verif\ParcoursState;
use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\FormationDemande;
use App\Entity\User;
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
use App\Service\LheoXML;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/formation')]
class FormationController extends BaseController
{
    #[Route('/', name: 'app_formation_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('formation/index.html.twig');
    }

    #[Route('/liste-cfvu', name: 'app_formation_liste_cfvu', methods: ['GET'])]
    public function listeCfvu(
        MentionRepository     $mentionRepository,
        ComposanteRepository  $composanteRepository,
        TypeDiplomeRepository $typeDiplomeRepository,
        FormationRepository   $formationRepository,
        Request               $request
    ) {
        $q = $request->query->get('q') ?? null;

        if ($this->isGranted('CAN_ETABLISSEMENT_CONSEILLER_ALL')) {
            $formationsCfvu = $formationRepository->findBySearchAndCfvu($q, $this->getAnneeUniversitaire(), $request->query->all());
            $isCfvu = true;
        }

        return $this->render('formation/_liste.html.twig', [
            'formations' => $formationsCfvu,
            'mentions' => $mentionRepository->findBy([], ['libelle' => 'ASC']),
            'composantes' => $composanteRepository->findBy([], ['libelle' => 'ASC']),
            'typeDiplomes' => $typeDiplomeRepository->findBy([], ['libelle' => 'ASC']),
            'params' => $request->query->all(),
            'isCfvu' => $isCfvu ?? false,
        ]);
    }

    #[Route('/liste', name: 'app_formation_liste', methods: ['GET'])]
    public function liste(
        MentionRepository     $mentionRepository,
        ComposanteRepository  $composanteRepository,
        TypeDiplomeRepository $typeDiplomeRepository,
        FormationRepository   $formationRepository,
        Request               $request,
    ): Response {
        $sort = $request->query->get('sort') ?? 'typeDiplome';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;
        $isCfvu = false;
        if ($this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_SES') ||
            $this->isGranted('CAN_COMPOSANTE_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('CAN_ETABLISSEMENT_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('CAN_FORMATION_SHOW_ALL', $this->getUser())) {
            $formations = $formationRepository->findBySearch($q, $this->getAnneeUniversitaire(), $request->query->all());
        } else {
            $formations = [];
            //gérer le cas ou l'utilisateur dispose des droits pour lire la composante
            $centres = $this->getUser()?->getUserCentres();
            foreach ($centres as $centre) {
                //todo: gérer avec un voter
                if ($centre->getComposante() !== null && (
                    in_array('Gestionnaire', $centre->getDroits()) ||
                    in_array('Invité', $centre->getDroits()) ||
                    in_array('Directeur', $centre->getDroits())
                )) {
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
            'mentions' => $mentionRepository->findBy([], ['libelle' => 'ASC']),
            'composantes' => $composanteRepository->findBy([], ['libelle' => 'ASC']),
            'typeDiplomes' => $typeDiplomeRepository->findBy([], ['libelle' => 'ASC']),
            'params' => $request->query->all(),
            'isCfvu' => false,
        ]);
    }

    #[Route('/liste/{composante}', name: 'app_formation_liste_composante', methods: ['GET'])]
    public function listeComposante(
        MentionRepository     $mentionRepository,
        TypeDiplomeRepository $typeDiplomeRepository,
        ComposanteRepository  $composanteRepository,
        FormationRepository   $formationRepository,
        Composante            $composante,
        Request               $request
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
            'direction' => $direction,
            'composantes' => $composanteRepository->findAll(),
            'typeDiplomes' => $typeDiplomeRepository->findAll(),
            'mentions' => $mentionRepository->findAll(),
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

    #[Route('/edit/formation/{slug}', name: 'app_formation_edit_modal', methods: ['GET', 'POST'])]
    public function editModal(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface   $entityManager,
        MentionRepository        $mentionRepository,
        Request                  $request,
        FormationRepository      $formationRepository,
        Formation                $formation
    ): Response {
        $form = $this->createForm(FormationSesType::class, $formation, [
            'action' => $this->generateUrl('app_formation_edit_modal', ['slug' => $formation->getSlug()]),
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
                $event = new AddCentreFormationEvent($formation, $changeSet['responsableMention'][0], ['ROLE_RESP_FORMATION']);
                $eventDispatcher->dispatch($event, AddCentreFormationEvent::REMOVE_CENTRE_FORMATION);
                // ajouter le nouveau resp, ajouter centre et droits et envoyer mail
                $event = new AddCentreFormationEvent($formation, $changeSet['responsableMention'][1], ['ROLE_RESP_FORMATION']);
                $eventDispatcher->dispatch($event, AddCentreFormationEvent::ADD_CENTRE_FORMATION);
            }

            $formationRepository->save($formation, true);

            return $this->redirectToRoute('app_formation_edit', ['slug' => $formation->getSlug()]);
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
    #[Route('/{slug}', name: 'app_formation_show', methods: ['GET'])]
    public function show(
        TypeDiplomeRegistry     $typeDiplomeRegistry,
        Formation               $formation,
        CalculStructureParcours $calculStructureParcours
    ): Response {
        $typeDiplome = $formation->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplôme non trouvé');
        }

        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

        $tParcours = [];
        foreach ($formation->getParcours() as $parcours) {
            $tParcours[$parcours->getId()] = $typeD->calculStructureParcours($parcours);
        }

        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'tParcours' => $tParcours,
            'typeD' => $typeD
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{slug}/edit', name: 'app_formation_edit', methods: ['GET', 'POST'])]
    public function edit(
        ParcoursState       $parcoursState,
        FormationState      $formationState,
        Request             $request,
        Formation           $formation,
        TypeDiplomeRegistry $typeDiplomeRegistry
    ): Response {
        if (!$this->isGranted('CAN_FORMATION_EDIT_MY', $formation)) {
            return $this->redirectToRoute('app_formation_show', ['slug' => $formation->getSlug()]);
        }

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

    #[Route('/api/{slug}/maquette_iframe', name: 'app_formation_maquette_iframe')]
    public function getFormationMaquetteIframe(Formation $formation, CalculStructureParcours $calcul) : Response {
        $listeParcours = [];

        foreach($formation->getParcours() as $parcours){
            $listeParcours[] = $calcul->calcul($parcours);
        }

        return $this->render('formation/maquette_iframe.html.twig', [
            'listeParcours' => $listeParcours
        ]);
    }
}
