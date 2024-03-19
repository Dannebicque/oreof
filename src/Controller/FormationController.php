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
use App\Classes\GetFormations;
use App\Classes\verif\FormationState;
use App\Classes\verif\ParcoursState;
use App\DTO\StatsFichesMatieres;
use App\Entity\Composante;
use App\Entity\Formation;
use App\Entity\FormationDemande;
use App\Entity\Parcours;
use App\Entity\ParcoursVersioning;
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
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Jfcherng\Diff\DiffHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
            $formationsCfvu = $formationRepository->findBySearchAndCfvu($q, $this->getDpe(), $request->query->all());
            $isCfvu = true;
        }

        return $this->render('formation/_liste.html.twig', [
            'formations' => $formationsCfvu,
            'mentions' => $mentionRepository->findBy([], ['libelle' => 'ASC']),
            'composantes' => $composanteRepository->findPorteuse(),
            'typeDiplomes' => $typeDiplomeRepository->findBy([], ['libelle' => 'ASC']),
            'params' => $request->query->all(),
            'isCfvu' => $isCfvu ?? false,
        ]);
    }

    #[Route('/liste', name: 'app_formation_liste', methods: ['GET'])]
    public function liste(
        GetFormations         $getFormations,
        MentionRepository     $mentionRepository,
        ComposanteRepository  $composanteRepository,
        TypeDiplomeRepository $typeDiplomeRepository,
        Request               $request,
    ): Response {
        $isCfvu = false;

        $tFormations = $getFormations->getFormations(
            $this->getUser(),
            $this->getDpe(),
            $request->query->all(),
            $isCfvu
        );

        return $this->render('formation/_liste.html.twig', [
            'formations' => $tFormations,
            'mentions' => $mentionRepository->findBy([], ['libelle' => 'ASC']),
            'composantes' => $composanteRepository->findPorteuse(),
            'typeDiplomes' => $typeDiplomeRepository->findBy([], ['libelle' => 'ASC']),
            'params' => $request->query->all(),
            'isCfvu' => false,
        ]);
    }

    #[Route('/detail/parcours/', name: 'app_parcours_formation_detail', methods: ['GET'])]
    public function parcoursFormation(
        Request $request,
        FormationRepository $formationRepository,
    ): Response
    {
        if ($request->query->has('formation')) {
            $formation = $formationRepository->find($request->query->get('formation'));
        } else {
            throw new \Exception('Formation non trouvée');
        }

        return $this->render('formation/_parcoursFormation.html.twig', [
            'formation' => $formation,
            'parcours' => $formation->getParcours()
        ]);
    }

    #[Route('/fiches/liste', name: 'app_fiches_formation_liste', methods: ['GET'])]
    public function fichesFormation(
        CalculStructureParcours $calculStructureParcours,
        GetFormations         $getFormations,
        MentionRepository     $mentionRepository,
        ComposanteRepository  $composanteRepository,
        TypeDiplomeRepository $typeDiplomeRepository,
        Request               $request,
    ): Response {

        $tFormations = $getFormations->getFormations(
            $this->getUser(),
            $this->getDpe(),
            $request->query->all()
        );

        $stats = [];
        foreach ($tFormations as $formation) {

            $parcourss = $formation->getParcours();
            $stats[$formation->getId()]['stats'] = new StatsFichesMatieres();

            foreach ($parcourss as $parcours) {
                $stats[$formation->getId()][$parcours->getId()] = $calculStructureParcours->calcul($parcours, false, false);
                $stats[$formation->getId()]['stats']->addStatsParcours(
                    $stats[$formation->getId()][$parcours->getId()]->statsFichesMatieresParcours
                );
            }
        }

        return $this->render('formation/_fichesListe.html.twig', [
            'formations' => $tFormations,
            'mentions' => $mentionRepository->findBy([], ['libelle' => 'ASC']),
            'composantes' => $composanteRepository->findPorteuse(),
            'typeDiplomes' => $typeDiplomeRepository->findBy([], ['libelle' => 'ASC']),
            'params' => $request->query->all(),
            'isCfvu' => false,
            'stats' => $stats
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
        $q = $request->query->get('q') ?? null;

        $formations = $formationRepository->findBySearch(
            $q,
            $this->getDpe(),
            $request->query->all(),
            $composante
        );

        return $this->render('formation/_liste.html.twig', [
            'formations' => $formations,
            'params' => $request->query->all(),
            'isCfvu' => false,
            'composantes' => $composanteRepository->findPorteuse(),
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

        $formation = new Formation($this->getDpe());
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
        EntityManagerInterface $entityManager
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

        /**
         * VERSIONING PARCOURS PAR DÉFAUT
         */
        $cssDiff = "";
        if($formation->isHasParcours() === false && count($formation->getParcours()) === 1){
            $cssDiff = DiffHelper::getStyleSheet();

            // Serializer
            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $serializer = new Serializer(
            [
                new DateTimeNormalizer(),
                new BackedEnumNormalizer(),
                new ArrayDenormalizer(),
                new ObjectNormalizer($classMetadataFactory, propertyTypeExtractor: new ReflectionExtractor()),
            ],
                [new JsonEncoder()]
            ); 
            // Version n-1     
            $lastVersion = $entityManager->getRepository(ParcoursVersioning::class)->findLastVersion($parcours);
            $lastVersion = count($lastVersion) > 0 ? $lastVersion[0] : null;

            $textDifferences = [];

            // Changements dans le texte : comparaison avec n-1
            if($lastVersion){
                $rendererName = 'Inline';
                $differOptions = [
                    'context' => 1,
                    'ignoreWhitespace' => true,
                    'ignoreLineEnding' => true,
                ];
                $rendererOptions = [
                    'detailLevel' => 'char',
                    'lineNumbers' => false,
                    'showHeader' => false,
                    'separateBlock' => false,
                ];
                $fileParcours = file_get_contents(__DIR__ . "/../../versioning_json/parcours/"
                                                . "{$lastVersion->getParcours()->getId()}/"
                                                . "{$lastVersion->getParcoursFileName()}.json"
                                );
                $lastVersion = $serializer->deserialize($fileParcours, Parcours::class, 'json');
                $textDifferences = [
                    'presentationParcoursContenuFormation' => html_entity_decode(DiffHelper::calculate(
                        $lastVersion->getFormation()->getContenuFormation() ?? "",
                        $parcours->getFormation()->getContenuFormation() ?? "",
                        $rendererName,
                        $differOptions,
                        $rendererOptions
                    )),
                    'presentationParcoursObjectifsParcours' => html_entity_decode(DiffHelper::calculate(
                        $lastVersion->getObjectifsParcours() ?? "",
                        $parcours->getObjectifsParcours() ?? "",
                        $rendererName,
                        $differOptions,
                        $rendererOptions
                    )),
                    'presentationParcoursResultatsAttendus' => html_entity_decode(DiffHelper::calculate(
                        $lastVersion->getFormation()->getResultatsAttendus() ?? "",
                        $parcours->getFormation()->getResultatsAttendus() ?? "",
                        $rendererName,
                        $differOptions,
                        $rendererOptions
                    )),
                    'presentationFormationObjectifsFormation' => html_entity_decode(DiffHelper::calculate(
                        $lastVersion->getFormation()?->getObjectifsFormation() ?? "",
                        $parcours->getFormation()?->getObjectifsFormation() ?? "",
                        $rendererName,
                        $differOptions,
                        $rendererOptions
                    )),
                ];
            }
        }
        /* FIN DU VERSIONING */

        return $this->render('formation/show.html.twig', [
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'tParcours' => $tParcours,
            'typeD' => $typeD,
            'cssDiff' => $cssDiff,
            'stringDifferences' => $textDifferences ?? [],
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

    #[Route('/{slug}/maquette_iframe', name: 'app_formation_maquette_iframe')]
    public function getFormationMaquetteIframe(Formation $formation, CalculStructureParcours $calcul) : Response
    {
        $listeParcours = [];

        foreach($formation->getParcours() as $parcours) {
            $listeParcours[] = $calcul->calcul($parcours);
        }

        return $this->render('formation/maquette_iframe.html.twig', [
            'listeParcours' => $listeParcours
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/mention/list', name: 'app_formation_liste_id')]
    public function getParcoursListForFormations(
        EntityManagerInterface $entityManager
    ) : Response {
        
        $formations = $entityManager->getRepository(Formation::class)->findBy(['dpe' => 1]);

        return $this->render("formation/parcours_list_with_id.html.twig", [
            'formations' => $formations
        ]);
    }
}
