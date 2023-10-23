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
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseIsUnprocessable;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

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
            'dto' => $dto,
            'isBut' => $typeDiplome->getLibelleCourt() === 'BUT',
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
        Request                $request,
        ParcoursDupliquer      $parcoursDupliquer,
        Parcours               $parcours,
    ): Response {

        $typeDuplication = JsonRequest::getValueFromRequest($request, 'dupliquer');

        if ($typeDuplication === 'recopie') {
           return  $parcoursDupliquer->recopie($parcours);
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

    /**
     * Retire les <div></div> et <!--block-->
     * et renvoie la chaîne épurée
     * @param ?string $stringToClean Chaîne de caractère à nettoyer
     * @return string Chaîne nettoyée
     */
    public function removeHTMLEntitiesFromString(?string $stringToClean) : string {
        if($stringToClean !== null && gettype($stringToClean) === 'string'){
            $cleanedString = preg_replace('/(\&nbsp;)+/', '', $stringToClean);
            $cleanedString = preg_replace('/(<([^>]+)>)/', '', $cleanedString);
            return $cleanedString;
        }
        else {
            return "";
        }
    }

    /**
     * Génère du contenu XML de l'offre de formation, à partir d'un parcours.
     * Le format est généré selon les spécifications du LHEO
     * @param Parcours $parcours Parcours à transformer en XML
     * @return string $xml Contenu XML
     */
    public function generateLheoXMLFromParcours(Parcours $parcours) : string {
        // Paramètres de l'encodeur
        $contextOptions = [
            'xml_root_node_name' => 'lheo',
            'xml_format_output' => true,
        ];

        //Récupération des valeurs
        // Codes ROME
        $codesRome = [];
        foreach($parcours->getCodesRome() as $code){
            $codesRome[] = $code['code'];
        }
        // Intitulé de la formation
        $intituleFormation = $parcours->getFormation()->getTypeDiplome()->getLibelle() . " " . $parcours->getLibelle();

        // Génération du XML
        $encoder = new XmlEncoder();
        $xml = $encoder->encode([
            // Attribut de l'élément racine
            '@xmlns' => 'http://lheo.gouv.fr/2.3',
            '@xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
            '@xsi:schemaLocation' => 'http://lheo.gouv.fr/2.3/lheo.xsd',
            'offres' => [ 
                'formation' => [
                    'domaine-formation' => [
                        // Formacode et code nsf optionnels
                        // 'code-FORMACODE' => '',
                        // 'code-NSF' => '',
                        'code-ROME' => $codesRome,
                    ],
                    'intitule-formation' => $intituleFormation,
                    'objectif-formation' => $this->removeHTMLEntitiesFromString($parcours->getObjectifsParcours()),
                    'resultats-attendus' => $this->removeHTMLEntitiesFromString($parcours->getResultatsAttendus()),
                    'contenu-formation' => $this->removeHTMLEntitiesFromString($parcours->getContenuFormation()),
                    // Tous les parcours sont certifiants ?
                    'certifiante' => 1,
                    'contact-formation' => [
                        // Référent pédagogique
                        'type-contact' => 3,
                        'coordonnees' => [
                            'nom' => $parcours->getRespParcours()->getNom(),
                            'prenom' => $parcours->getRespParcours()->getPrenom(),
                            'courriel' => $parcours->getRespParcours()->getEmail(),
                            ]
                        ],
                    // tous les parcours sont en groupe (non personnalisés) ?
                    'parcours-de-formation' => 1,
                    'code-niveau-entree' => $parcours->getFormation()->getNiveauEntree()->value,
                    'action' => [
                        'rythme-formation' => $this->removeHTMLEntitiesFromString($parcours->getRythmeFormationTexte()),
                        // Code FORMACODE
                        'code-public-vise' => '31057', // A CHANGER
                        'niveau-entree-obligatoire' => 1,
                        'modalites-alternance' => $this->removeHTMLEntitiesFromString($parcours->getModalitesAlternance()),
                        'modalites-enseignement' => $parcours->getModalitesEnseignement()->value,
                        'conditions-specifiques' => 'Aucune', // A CHANGER
                        'prise-en-charge-frais-possible' => 1, // A CHANGER - 1 oui | 0 non
                        'modalites-entrees-sorties' => 0, // Entrées sorties à dates fixes : 0 | entrées / sorties permanentes : 1
                        'session' => [
                            'periode' => [
                                'debut' => '00000000', //AAAAMMJJ - A CHANGER
                                'fin' => '00000000' // AAAAMMJJ - A CHANGER
                            ],
                            'adresse-inscription' => [
                                'adresse' => [
                                    'ligne' => 'XXXXXXX', // A CHANGER
                                    'codepostal' => '51100', // A CHANGER
                                    'ville' => 'REIMS', // A CHANGER
                                ]
                            ]
                        ]

                    ],
                    'organisme-formation-responsable' => [
                        'numero-activite' => 'XXXXXXXXXXX', // A CHANGER 
                        'SIRET-organisme-formation' => ['SIRET' => '19511296600799'], // A VERIFIER
                        'nom-organisme' => 'UNIVERSITE DE REIMS CHAMPAGNE-ARDENNE (URCA)',
                        'raison-sociale' => 'XXXXXXXXXXXX', // A CHANGER
                        'coordonnees-organisme' => [
                            'coordonnees' => [
                                // Coordonnées complètes de l'organisme responsable de l'offre
                                // COORDONNEES DU SECRETARIAT ?
                            ]
                        ],
                        'contact-organisme' => [
                            'coordonnees' => [
                                // Coordonnées d'une personne de l'organisme responsable de l'offre
                                // Quelles coordonnées ?
                            ]
                        ]
                    ]
                ]
            ]

        ], 'xml', $contextOptions);

        return $xml;
       
    }

    /**
     * Valide le XML selon le format LHEO
     * @param string $xml Chaîne contenant le XML
     * @return bool Vrai si le XML est valide, Faux sinon
     */
    public function validateLheoSchema(string $xml) : bool {
        
        $xmlValidator = new \DOMDocument();
        $xmlValidator->loadXML($xml);
        $isValid = $xmlValidator->schemaValidate(__DIR__ . '/../../lheo.xsd');
        
        return $isValid;
    }

    #[Route('/{parcours}/export-xml-lheo', name: 'app_parcours_export_xml_lheo')]
    public function getXmlLheoFromParcours(Parcours $parcours) : Response {
        $xml = $this->generateLheoXMLFromParcours($parcours);
        // Validation
        libxml_use_internal_errors(true);
        $isValid = $this->validateLheoSchema($xml);
        $htmlValidator = '<h1>Le schéma XML est valide !<h1>';
        if(!$isValid){
            $htmlValidator = "";
            $xml_errors = libxml_get_errors();
            foreach($xml_errors as $error){
                $htmlValidator .= "<p>Line : {$error->line} | {$error->message}</p>";
            }
        }
        libxml_clear_errors();
        // Si le XML généré est valide, on le renvoie
        if($isValid){
            return new Response($xml, 200, ['Content-Type' => 'application/xml']);
        }
        // Sinon, on avertit le client
        else {
            return new Response("<p>La ressource demandée est incomplète ou invalide</p>" . $htmlValidator, 422, [
                'Content-Type' => 'text/html'
            ]);
        }
    }
}
