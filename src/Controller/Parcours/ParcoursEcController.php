<?php
/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Parcours/ParcoursV2Controller.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/01/2026 22:28
 */

namespace App\Controller\Parcours;

use App\Classes\EcOrdre;
use App\Classes\GetElementConstitutif;
use App\Controller\BaseController;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\TypeEc;
use App\Entity\Ue;
use App\Form\EcStep4Type;
use App\Form\ElementConstitutifType;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\NatureUeEcRepository;
use App\Repository\TypeEcRepository;
use App\Service\Validation\ValidationDirtyMarker;
use App\TypeDiplome\TypeDiplomeResolver;
use App\Utils\TurboStreamResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/parcours/v2/ec', name: 'parcours_ec')]
#[IsGranted('ROLE_ADMIN')]
class ParcoursEcController extends BaseController
{

    #[Route('/{parcours}/ajouter-ec/{ue}', name: '_ajouter_ec_ue', methods: ['GET', 'POST'])]
    public function new(
        TurboStreamResponseFactory   $turboStream,
        NatureUeEcRepository         $natureUeEcRepository,
        EcOrdre                      $ecOrdre,
        Request                      $request,
        TypeEcRepository             $typeEcRepository,
        FicheMatiereRepository       $ficheMatiereRepository,
        ElementConstitutifRepository $elementConstitutifRepository,
        Ue                           $ue,
        Parcours                     $parcours
    ): Response
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $elementConstitutif = new ElementConstitutif();
        $elementConstitutif->setFicheMatiere(null);
        $elementConstitutif->setParcours($parcours);

        $elementConstitutif->setModaliteEnseignement($parcours->getModalitesEnseignement());
        $elementConstitutif->setUe($ue);

        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl(
                'parcours_ec_ajouter_ec_ue',
                ['ue' => $ue->getId(), 'parcours' => $parcours->getId(), 'element' => $request->query->get('element')]
            ),
            'typeDiplome' => $typeDiplome,
            'formation' => $parcours->getFormation(),
            'isAdmin' => $isAdmin
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('typeEcTexte')->getData() !== null && $form->get('typeEc')->getData() === null) {
                $tu = new TypeEc();
                $tu->setLibelle($form->get('typeEcTexte')->getData());
                $tu->addTypeDiplome($typeDiplome);
                $tu->setFormation($parcours->getFormation());
                $typeEcRepository->save($tu, true);
                $elementConstitutif->setTypeEc($tu);
            }

            if ($elementConstitutif->getNatureUeEc()?->isChoix() === false and $elementConstitutif->getNatureUeEc()?->isLibre() === false) {
                if (str_starts_with($request->request->get('ficheMatiere'), 'id_')) {
                    $ficheMatiere = $ficheMatiereRepository->find((int)str_replace(
                        'id_',
                        '',
                        $request->request->get('ficheMatiere')
                    ));
                } else {
                    $ficheMatiere = new FicheMatiere();
                    $ficheMatiere->setCampagneCollecte($this->getCampagneCollecte());
                    $ficheMatiere->setLibelle($request->request->get('ficheMatiereLibelle'));
                    $ficheMatiere->setParcours($parcours);
                    $ficheMatiereRepository->save($ficheMatiere, true);
                }
                $lastEc = $ecOrdre->getOrdreSuivant($ue, $request);
                $elementConstitutif->setFicheMatiere($ficheMatiere);
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
            } elseif ($elementConstitutif->getNatureUeEc()?->isChoix() === true) {
                $lastEc = $ecOrdre->getOrdreSuivant($ue, $request);
                $elementConstitutif->setLibelle($request->request->get('ficheMatiereLibre'));
                $elementConstitutif->setFicheMatiere(null);
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
                //on récupère le champs matières, on découpe selon la ,. Si ca commence par "id_", on récupère la matière, sinon on créé la matière
                $matieres = explode(',', $request->request->get('matieres'));
                $natureEc = $natureUeEcRepository->findOneBy(['choix' => false, 'libre' => false, 'type' => 'ec']);
                foreach ($matieres as $matiere) {
                    $ec = new ElementConstitutif();
                    $nextSousEc = $ecOrdre->getOrdreEnfantSuivant($elementConstitutif);
                    $ec->setEcParent($elementConstitutif);
                    $ec->setParcours($parcours);
                    $ec->setModaliteEnseignement($parcours?->getModalitesEnseignement());
                    $ec->setUe($ue);
                    $ec->setNatureUeEc($natureEc);

                    if (str_starts_with($matiere, 'id_')) {
                        $ficheMatiere = $ficheMatiereRepository->find((int)str_replace('id_', '', $matiere));
                    } else {
                        $ficheMatiere = new FicheMatiere();
                        $ficheMatiere->setCampagneCollecte($this->getCampagneCollecte());
                        $ficheMatiere->setLibelle(str_replace('ac_', '', $matiere));
                        $ficheMatiere->setParcours($parcours); //todo: ajouter le semestre
                        $ficheMatiereRepository->save($ficheMatiere, true);
                    }

                    $ec->setFicheMatiere($ficheMatiere);
                    $ec->setOrdre($nextSousEc);
                    $ec->genereCode();
                    $elementConstitutifRepository->save($ec, true);
                }
            } else {
                $lastEc = $ecOrdre->getOrdreSuivant($ue, $request);
                $elementConstitutif->setTexteEcLibre($request->request->get('ficheMatiereLibre'));
                $elementConstitutif->setLibelle($request->request->get('ficheMatiereLibreLibelle'));
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
            }

            return $this->json(true);
        }

        return $turboStream->streamOpenModalFromTemplates(
            'Ajouter une EC',
            'Dans l\UE ' . $ue->display(),
            'parcours_v2/ec/_new.html.twig',
            [
                'elementConstitutif' => $elementConstitutif,
                'form' => $form->createView(),
                'ue' => $ue,
                'parcours' => $parcours,
                'isAdmin' => $isAdmin,
            ],
            '_ui/_footer_submit_cancel.html.twig',
            [
                'submitLabel' => 'Créer l\'EC',
            ]
        );


    }


    #[Route('/{parcours}/modifier-ec/{elementConstitutif}', name: '_modifier_ec_ue', methods: ['GET', 'POST'])]
    public function modifier(
        TurboStreamResponseFactory   $turboStream,
        NatureUeEcRepository         $natureUeEcRepository,
        EcOrdre                      $ecOrdre,
        Request                      $request,
        TypeEcRepository             $typeEcRepository,
        FicheMatiereRepository       $ficheMatiereRepository,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif           $elementConstitutif,
        Parcours                     $parcours
    ): Response
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();
        $ue = $elementConstitutif->getUe();

        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl(
                'parcours_ec_ajouter_ec_ue',
                ['ue' => $ue->getId(), 'parcours' => $parcours->getId(), 'element' => $request->query->get('element')]
            ),
            'typeDiplome' => $typeDiplome,
            'formation' => $parcours->getFormation(),
            'isAdmin' => $isAdmin
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('typeEcTexte')->getData() !== null && $form->get('typeEc')->getData() === null) {
                $tu = new TypeEc();
                $tu->setLibelle($form->get('typeEcTexte')->getData());
                $tu->addTypeDiplome($typeDiplome);
                $tu->setFormation($parcours->getFormation());
                $typeEcRepository->save($tu, true);
                $elementConstitutif->setTypeEc($tu);
            }

            if ($elementConstitutif->getNatureUeEc()?->isChoix() === false and $elementConstitutif->getNatureUeEc()?->isLibre() === false) {
                if (str_starts_with($request->request->get('ficheMatiere'), 'id_')) {
                    $ficheMatiere = $ficheMatiereRepository->find((int)str_replace(
                        'id_',
                        '',
                        $request->request->get('ficheMatiere')
                    ));
                } else {
                    $ficheMatiere = new FicheMatiere();
                    $ficheMatiere->setCampagneCollecte($this->getCampagneCollecte());
                    $ficheMatiere->setLibelle($request->request->get('ficheMatiereLibelle'));
                    $ficheMatiere->setParcours($parcours);
                    $ficheMatiereRepository->save($ficheMatiere, true);
                }
                $lastEc = $ecOrdre->getOrdreSuivant($ue, $request);
                $elementConstitutif->setFicheMatiere($ficheMatiere);
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
            } elseif ($elementConstitutif->getNatureUeEc()?->isChoix() === true) {
                $lastEc = $ecOrdre->getOrdreSuivant($ue, $request);
                $elementConstitutif->setLibelle($request->request->get('ficheMatiereLibre'));
                $elementConstitutif->setFicheMatiere(null);
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
                //on récupère le champs matières, on découpe selon la ,. Si ca commence par "id_", on récupère la matière, sinon on créé la matière
                $matieres = explode(',', $request->request->get('matieres'));
                $natureEc = $natureUeEcRepository->findOneBy(['choix' => false, 'libre' => false, 'type' => 'ec']);
                foreach ($matieres as $matiere) {
                    $ec = new ElementConstitutif();
                    $nextSousEc = $ecOrdre->getOrdreEnfantSuivant($elementConstitutif);
                    $ec->setEcParent($elementConstitutif);
                    $ec->setParcours($parcours);
                    $ec->setModaliteEnseignement($parcours?->getModalitesEnseignement());
                    $ec->setUe($ue);
                    $ec->setNatureUeEc($natureEc);

                    if (str_starts_with($matiere, 'id_')) {
                        $ficheMatiere = $ficheMatiereRepository->find((int)str_replace('id_', '', $matiere));
                    } else {
                        $ficheMatiere = new FicheMatiere();
                        $ficheMatiere->setCampagneCollecte($this->getCampagneCollecte());
                        $ficheMatiere->setLibelle(str_replace('ac_', '', $matiere));
                        $ficheMatiere->setParcours($parcours); //todo: ajouter le semestre
                        $ficheMatiereRepository->save($ficheMatiere, true);
                    }

                    $ec->setFicheMatiere($ficheMatiere);
                    $ec->setOrdre($nextSousEc);
                    $ec->genereCode();
                    $elementConstitutifRepository->save($ec, true);
                }
            } else {
                $lastEc = $ecOrdre->getOrdreSuivant($ue, $request);
                $elementConstitutif->setTexteEcLibre($request->request->get('ficheMatiereLibre'));
                $elementConstitutif->setLibelle($request->request->get('ficheMatiereLibreLibelle'));
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
            }

            return $this->json(true);
        }

        return $turboStream->streamOpenModalFromTemplates(
            'Modifier une EC',
            'EC :  ' . $elementConstitutif->display(),
            'parcours_v2/ec/_new.html.twig',
            [
                'elementConstitutif' => $elementConstitutif,
                'form' => $form->createView(),
                'ue' => $ue,
                'parcours' => $parcours,
                'isAdmin' => $isAdmin,
            ],
            '_ui/_footer_submit_cancel.html.twig',
            [
                'submitLabel' => 'Créer l\'EC',
            ]
        );


    }

    #[Route('/{parcours}/saisie-heure/{id}', name: '_saisir_heures', methods: ['GET'])]
    public function saisirHeures(Parcours $parcours, ElementConstitutif $elementConstitutif): Response
    {
        $isParcoursProprietaire = $elementConstitutif->getFicheMatiere()?->getParcours()?->getId() === $parcours->getId();

        $getElement = new GetElementConstitutif($elementConstitutif, $parcours);
        $ecHeures = $getElement->getFicheMatiereHeures();

        $form = $this->createForm(EcStep4Type::class, $ecHeures, [
            'isModal' => true,
            'data_class' => $ecHeures::class,
            'modalite' => $parcours->getModalitesEnseignement(),
            'action' => $this->generateUrl(
                'app_element_constitutif_structure',
                [
                    'id' => $elementConstitutif->getId(),
                    'parcours' => $parcours->getId()
                ]
            ),
        ]);

        $body = $this->renderView('parcours_v2/ec/_heures_ec.html.twig', [
            'form' => $form->createView(),
            'ec' => $elementConstitutif,
            'parcours' => $parcours,
            'isParcoursProprietaire' => $isParcoursProprietaire,
            'modalite' => $parcours->getModalitesEnseignement()
        ]);

        $footer = $this->renderView('_ui/_footer_submit_cancel.html.twig', [
            'submitLabel' => 'Enregistrer les heures de l\'EC',
        ]);

        return new Response(
            $this->renderView('_ui/open.stream.html.twig', [
                'title' => 'Modifier les heures de l\'EC',
                'subtitle' => 'Dans : EC ' . $elementConstitutif->display(),
                'body' => $body,
                'footer' => $footer,
            ]),
            200,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );
    }

    #[Route('/{parcours}/saisie-heure/{id}', name: '_saisir_heures_post', methods: ['POST'])]
    public function ecPost(
        ValidationDirtyMarker $dirtyMarker,
        Request               $request,
        Parcours              $parcours,
        ElementConstitutif    $id
    ): Response
    {
        // Turbo Stream si Turbo le demande (Accept: text/vnd.turbo-stream.html)
        $accept = $request->headers->get('Accept', '');
        $wantsTurboStream = str_contains($accept, 'text/vnd.turbo-stream.html');
        $dirtyMarker->markEcDirty($id);

        if ($wantsTurboStream) {
            return $this->render('parcours_v2/ec/_heures_ec_valide.html.twig', [
                'ec' => $id,
                'parcours' => $parcours,
            ], new Response(headers: ['Content-Type' => 'text/vnd.turbo-stream.html']));
        }
        //todo: ?? où
    }
}
