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
use App\Classes\JsonReponse;
use App\Classes\ParcoursDupliquer;
use App\Classes\verif\ParcoursState;
use App\DTO\HeuresEctsFormation;
use App\DTO\HeuresEctsSemestre;
use App\DTO\HeuresEctsUe;
use App\Entity\AnneeUniversitaire;
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\ParcoursVersioning;
use App\Entity\SemestreParcours;
use App\Events\AddCentreParcoursEvent;
use App\Form\ParcoursType;
use App\Repository\ElementConstitutifRepository;
use App\Repository\ParcoursRepository;
use App\Service\LheoXML;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use DateTimeImmutable;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
        Request            $request,
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
            'direction' => $direction,
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
        TypeDiplomeRegistry $typeDiplomeRegistry,
        Parcours            $parcours,
        LheoXML             $lheoXML,
    ): Response {
        $formation = $parcours->getFormation();
        if ($formation === null) {
            throw $this->createNotFoundException();
        }
        $typeDiplome = $formation->getTypeDiplome();
        if ($typeDiplome === null) {
            throw $this->createNotFoundException();
        }

        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());
        $dto = $typeD->calculStructureParcours($parcours);

        return $this->render('parcours/show.html.twig', [
            'parcours' => $parcours,
            'formation' => $formation,
            'typeDiplome' => $typeDiplome,
            'hasParcours' => $formation->isHasParcours(),
            'dto' => $dto,
            'typeD' => $typeD,
            'lheoXML' => $lheoXML
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
        if (!$this->isGranted('CAN_PARCOURS_EDIT_MY', $parcour)) {
            return $this->redirectToRoute('app_parcours_show', ['id' => $parcour->getId()]);
        }

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

    #[Route('/{id}/dupliquer/modal', name: 'app_parcours_dupliquer_modal', methods: ['GET'])]
    public function dupliquerModal(
        EntityManagerInterface $entityManager,
        Parcours               $parcour,
    ): Response {
        return $this->render('parcours/_dupliquer.html.twig', [
            'parcours' => $parcour,
        ]);
    }

    #[Route('/{id}/dupliquer', name: 'app_parcours_dupliquer', methods: ['POST'])]
    public function dupliquer(
        Request           $request,
        ParcoursDupliquer $parcoursDupliquer,
        Parcours          $parcours,
    ): Response {
        $typeDuplication = JsonRequest::getValueFromRequest($request, 'dupliquer');

        if ($typeDuplication === 'recopie') {
            return $parcoursDupliquer->recopie($parcours);
        }

        if ($typeDuplication === 'mutualise') {
            // return $parcoursDupliquer->recopieAvecMutualise($parcours);
            return JsonReponse::error('Mutualisation non implémentée');
        }

        return JsonReponse::error('Modalite de recopie inconnue');
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

    #[Route('/{parcours}/maquette_iframe', name: 'app_parcours_maquette_iframe')]
    public function getMaquetteIframe(
        Parcours                     $parcours,
        EntityManagerInterface       $em,
        ElementConstitutifRepository $ecRepo
    ) {
        $calcul = new CalculStructureParcours($em, $ecRepo);

        return $this->render('parcours/maquette_iframe.html.twig', [
            'parcours' => $calcul->calcul($parcours)
        ]);
    }

    #[Route('/{parcours}/export-xml-lheo', name: 'app_parcours_export_xml_lheo')]
    public function getXmlLheoFromParcours(Parcours $parcours, LheoXML $lheoXML): Response
    {
        $xml = $lheoXML->generateLheoXMLFromParcours($parcours, true);
        // Validation
        libxml_use_internal_errors(true);
        $isValid = $lheoXML->validateLheoSchema($xml);
        $xml_errors = [];
        if (!$isValid) {
            foreach (libxml_get_errors() as $error) {
                $xml_errors[] = $lheoXML->decodeErrorMessages($error->message);
            }
        }
        libxml_clear_errors();
        // Si le XML généré est valide, on le renvoie
        if ($isValid) {
            return new Response($xml, 200, ['Content-Type' => 'application/xml']);
        } // Sinon, on avertit le client
        else {
            return $this->render('lheo/error.html.twig', [
                'errors' => $xml_errors
            ]);
        }
    }

    #[Route('/{parcours}/export-json-urca', name: 'app_parcours_export_json_urca')]
    public function getJsonExportUrca(
        Parcours            $parcours,
        TypeDiplomeRegistry $typeDiplomeRegistry,
    ): Response {
        $typeDiplome = $parcours->getFormation()->getTypeDiplome();
        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

        $ects = $typeD->calculStructureParcours($parcours)->heuresEctsFormation->sommeFormationEcts;

        // Gestion de la localisation
        // Vide par défaut : -
        $localisationMetadata = ["-"];
        // Si l'on a une ville sur le parcours
        if($parcours->getLocalisation()?->getLibelle() !== null){
            $localisationMetadata = [$parcours->getLocalisation()?->getLibelle()];
        }
        // Sinon on prend au niveau de la composante
        else {
            $villeArray = $parcours->getFormation()?->getLocalisationMention()?->toArray();
            if(count($villeArray) > 0){
                $localisationMetadata = array_map(
                    fn($ville) => $ville->getLibelle(),
                    $villeArray
                );
            }
        }

        $data = [
            'description' => "",
            'ects' => $ects ?? 0,
            'metadata' => [
                'domaine' => $parcours->getFormation()?->getDomaine()?->getLibelle() ?? '-',
                'type-formation' => $parcours->getFormation()?->getTypeDiplome()?->getLibelle() ?? '-',
                'localisation' => $localisationMetadata,
                'faculte-ecole-institut' => $parcours->getComposanteInscription()?->getLibelle() 
                    ?? $parcours->getFormation()?->getComposantePorteuse()?->getLibelle()
                    ?? '-',
                'public-concerne' => $parcours->getRegimeInscription() ?? [], //Certains sont des tableaux, d'autres en JSON
            ],
            'xml-lheo' => $this->generateUrl('app_parcours_export_xml_lheo', ['parcours' => $parcours->getId()], UrlGenerator::ABSOLUTE_URL),
            'fiche-pdf' => $this->generateUrl('app_parcours_export', ['parcours' => $parcours->getId()], UrlGenerator::ABSOLUTE_URL),
            'maquette-pdf' => $this->generateUrl('app_parcours_mccc_export', ['parcours' => $parcours->getId(), '_format' => 'pdf'], UrlGenerator::ABSOLUTE_URL), 'maquette-json' => $this->generateUrl('app_parcours_export_maquette_json', ['parcours' => $parcours->getId()], UrlGenerator::ABSOLUTE_URL),
        ];

        return new JsonResponse($data);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{parcours}/versioning/json_data', name: 'app_parcours_versioning_json_data')]
    public function displayParcoursJsonData(Parcours $parcours): Response
    {
        // Définition du serializer
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $serializer = new Serializer(
            [
            new DateTimeNormalizer(),
            new BackedEnumNormalizer(),
            new ObjectNormalizer($classMetadataFactory, propertyTypeExtractor: new ReflectionExtractor()),
        ],
            [new JsonEncoder()]
        );
        try {
            // Création de la réponse JSON au client
            $json = $serializer->serialize($parcours, 'json', [
                AbstractObjectNormalizer::GROUPS => ['parcours_json_versioning'],
                'circular_reference_limit' => 2,
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
            ]);
            return new Response($json, 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            // Si erreur lors de la serialization
            return new Response(json_encode([
                'error' => 'Une erreur interne est survenue.',
                'message' => "{$e->getMessage()}",
            ]), 422, ['Content-Type' => 'application/json']);
        }
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{parcours}/versioning/save', name: 'app_parcours_versioning_save')]
    public function saveParcoursIntoJson(
        Parcours               $parcours,
        Filesystem             $fileSystem,
        EntityManagerInterface $entityManager,
    ) {
        // Définition du serializer
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $serializer = new Serializer(
            [
            new DateTimeNormalizer(),
            new BackedEnumNormalizer(),
            new ObjectNormalizer($classMetadataFactory, propertyTypeExtractor: new ReflectionExtractor())
        ],
            [new JsonEncoder()]
        );
        try {
            $now = new DateTimeImmutable('now');
            $dateHeure = $now->format('d-m-Y_H-i-s');
            // Objet BD Parcours Versioning
            $parcoursVersioning = new ParcoursVersioning();
            $parcoursVersioning->setParcours($parcours);
            $parcoursVersioning->setVersionTimestamp($now);
            // Nom du fichier
            $fileName = "parcours-{$parcours->getId()}-{$dateHeure}";
            $parcoursVersioning->setFileName($fileName);
            // Création du fichier JSON
            $json = $serializer->serialize($parcours, 'json', [
                AbstractObjectNormalizer::GROUPS => ['parcours_json_versioning'],
                'circular_reference_limit' => 2,
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s',
            ]);
            $fileSystem->appendToFile(__DIR__ . "/../../versioning_json/parcours/{$fileName}.json", $json);
            // Enregistrement de la référence en BD
            $entityManager->persist($parcoursVersioning);
            $entityManager->flush();
            // Ajout dans les logs
            /**
             * @var User $user
             */
            $user = $this->getUser();
            $successLogTxt = "[{$dateHeure}] Le parcours {$parcours->getId()} a été versionné avec succès. ";
            $successLogTxt .= "Utilisateur : {$user->getPrenom()} {$user->getNom()} ({$user->getUsername()})\n";
            $fileSystem->appendToFile(__DIR__ . "/../../versioning_json/success_log/save_parcours_success.log", $successLogTxt);
            // Message de réussite + redirection
            $this->addFlashBag('success', 'La version du parcours à bien été sauvegardée.');
            return $this->redirectToRoute('app_parcours_show', ['id' => $parcours->getId()]);
        } catch (\Exception $e) {
            // Log error
            $logTxt = "[{$dateHeure}] Le versioning du parcours : {$parcours->getId()} a rencontré une erreur.\n{$e->getMessage()}\n";
            $fileSystem->appendToFile(__DIR__ . "/../../versioning_json/error_log/save_parcours_error.log", $logTxt);

            $this->addFlashBag('error', "Une erreur est survenue lors de la sauvegarde.");
            return $this->redirectToRoute('app_parcours_show', ['id' => $parcours->getId()]);
        }
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{parcours_versioning}/versioning/view', name: 'app_parcours_versioning_view')]
    public function parcoursVersion(
        ParcoursVersioning      $parcours_versioning,
        TypeDiplomeRegistry $typeDiplomeRegistry
    ): Response {
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
        $file = file_get_contents(__DIR__ . "/../../versioning_json/parcours/{$parcours_versioning->getFileName()}.json");
        $parcours = $serializer->deserialize($file, Parcours::class, 'json');

        $typeD = $typeDiplomeRegistry->getTypeDiplome($parcours->getFormation()?->getTypeDiplome()?->getModeleMcc());
        $dto = $typeD->calculStructureParcours($parcours);

        $dateVersion = $parcours_versioning->getVersionTimestamp()->format('d-m-Y à H:i');

        return $this->render('parcours/show_version.html.twig', [
            'parcours' => $parcours,
            'formation' => $parcours->getFormation(),
            'typeDiplome' => $parcours->getTypeDiplome(),
            'dto' => $dto,
            'hasParcours' => $parcours->getFormation()->isHasParcours(),
            // 'isBut' => $parcours->getTypeDiplome()->getLibelleCourt() === 'BUT',
            'dateVersion' => $dateVersion
        ]);
    }

    #[IsGranted('ROLE_SES')]
    #[Route('/check/lheo_invalid_list', name: 'app_parcours_lheo_invalid_list')]
    public function getInvalidXmlLheoList(
        LheoXML            $lheoXML,
        ParcoursRepository $parcoursRepo
    ): Response {
        $parcoursList = [
            ...$parcoursRepo->findByTypeValidation($this->getAnneeUniversitaire(), 'valide_pour_publication'),
            ...$parcoursRepo->findByTypeValidation($this->getAnneeUniversitaire(), 'publie'),
            ...$parcoursRepo->findByTypeValidation($this->getAnneeUniversitaire(), 'valide_a_publier')
        ];
        
        $errorArray = [];
        foreach ($parcoursList as $p) {
            $erreursChampsParcours = $lheoXML->checkTextValuesAreLongEnough($p);
            if ($lheoXML->isValidLHEO($p) === false || count($erreursChampsParcours) > 0) {
                $xmlErrorArray = [];
                foreach (libxml_get_errors() as $xmlError) {
                    $xmlErrorArray[] = $lheoXML->decodeErrorMessages($xmlError->message);
                }
                $xmlErrorArray = array_merge($xmlErrorArray, $erreursChampsParcours);
                $errorArray[] = [
                    'id' => $p->getId(),
                    'parcours_libelle' => $p->getLibelle(),
                    'formation_libelle' => $p->getFormation()?->getMention()?->getLibelle(),
                    'type_formation_libelle' => $p->getFormation()?->getTypeDiplome()?->getLibelle(),
                    'xml_errors' => $xmlErrorArray
                ];
                libxml_clear_errors();
            }
        }

        return $this->render('lheo/list.html.twig', [
            'errorArray' => $errorArray
        ]);
    }
}
