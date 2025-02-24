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
use App\Classes\GetElementConstitutif;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\TypeEc;
use App\Entity\Ue;
use App\Form\EcStep4Type;
use App\Form\ElementConstitutifEnfantType;
use App\Form\ElementConstitutifType;
use App\Form\FicheMatiereStep4Type;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\NatureUeEcRepository;
use App\Repository\TypeEcRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/element/constitutif')]
class ElementConstitutifController extends AbstractController
{
    #[Route('/', name: 'app_element_constitutif_index', methods: ['GET'])]
    public function index(FicheMatiereRepository $ficheMatiereRepository): Response
    {
        return $this->render('element_constitutif/index.html.twig', [
            'element_constitutifs' => $ficheMatiereRepository->findAll(),
        ]);
    }

    #[Route('/type-ec/{ue}/{parcours}', name: 'app_element_constitutif_type_ec', methods: ['GET'])]
    public function typeEc(
        ElementConstitutifRepository $elementConstitutifRepository,
        Request                      $request,
        FicheMatiereRepository       $ficheMatiereRepository,
        NatureUeEcRepository         $natureUeEcRepository,
        Ue                           $ue,
        Parcours                     $parcours
    ): Response {
        if ($request->query->has('ec')) {
            $ec = $elementConstitutifRepository->find($request->query->get('ec'));
        }

        if ($request->query->has('delete')) {
            $ec = $elementConstitutifRepository->find($request->query->get('delete'));
            $elementConstitutifRepository->remove($ec, true);
            return $this->json(true);
        }

        $natureEc = $natureUeEcRepository->find($request->query->get('choix'));
        if ($natureEc !== null) {
            $matieres[] = $ficheMatiereRepository->findByParcours($parcours);
            $matieres[] = $ficheMatiereRepository->findByComposante($parcours->getFormation()->getComposantePorteuse());
            $matieres = array_merge(...$matieres);

            if ($natureEc->isChoix() === true) {
                return $this->render('element_constitutif/_type_ec_matieres.html.twig', [
                    'ec' => $ec ?? null,
                    'matieres' => $matieres
                ]);
            }

            if ($natureEc->isLibre() === true) {
                return $this->render('element_constitutif/_type_ec_matieres_libre.html.twig', [
                    'ec' => $ec ?? null,
                ]);
            }

            return $this->render('element_constitutif/_type_ec_matiere.html.twig', [
                'ec' => $ec ?? null,
                'matieres' => $matieres
            ]);
        }

        throw new RuntimeException('Nature EC non trouvée');
    }

    #[Route('/new/{ue}/{parcours}', name: 'app_element_constitutif_new', methods: ['GET', 'POST'])]
    public function new(
        EcOrdre                      $ecOrdre,
        Request                      $request,
        TypeDiplomeRegistry          $typeDiplomeRegistry,
        TypeEcRepository             $typeEcRepository,
        FicheMatiereRepository       $ficheMatiereRepository,
        ElementConstitutifRepository $elementConstitutifRepository,
        Ue                           $ue,
        Parcours                     $parcours
    ): Response {
        $isAdmin = $this->isGranted('ROLE_SES');
        $elementConstitutif = new ElementConstitutif();
        $elementConstitutif->setFicheMatiere(null);
        $elementConstitutif->setParcours($parcours);

        $elementConstitutif->setModaliteEnseignement($parcours?->getModalitesEnseignement());
        $elementConstitutif->setUe($ue);
        $typeDiplome = $parcours->getFormation()->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplôme non trouvé');
        }

        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl(
                'app_element_constitutif_new',
                ['ue' => $ue->getId(), 'parcours' => $parcours->getId()]
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
                    $ficheMatiere->setLibelle($request->request->get('ficheMatiereLibelle'));
                    $ficheMatiere->setParcours($parcours); //todo: ajouter le semestre
                    $ficheMatiereRepository->save($ficheMatiere, true);
                }
                $lastEc = $ecOrdre->getOrdreSuivant($ue);
                $elementConstitutif->setFicheMatiere($ficheMatiere);
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
            } elseif ($elementConstitutif->getNatureUeEc()?->isChoix() === true) {
                $lastEc = $ecOrdre->getOrdreSuivant($ue);
                $elementConstitutif->setLibelle($request->request->get('ficheMatiereLibre'));
                $elementConstitutif->setFicheMatiere(null);
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
                //on récupère le champs matières, on découpe selon la ,. Si ca commence par "id_", on récupère la matière, sinon on créé la matière
                $matieres = explode(',', $request->request->get('matieres'));
                foreach ($matieres as $matiere) {
                    $ec = new ElementConstitutif();
                    $nextSousEc = $ecOrdre->getOrdreEnfantSuivant($elementConstitutif);
                    $ec->setEcParent($elementConstitutif);
                    $ec->setParcours($parcours);
                    $ec->setModaliteEnseignement($parcours?->getModalitesEnseignement());
                    $ec->setUe($ue);
                    $ec->setNatureUeEc($elementConstitutif->getNatureUeEc());

                    if (str_starts_with($matiere, 'id_')) {
                        $ficheMatiere = $ficheMatiereRepository->find((int)str_replace('id_', '', $matiere));
                    } else {
                        $ficheMatiere = new FicheMatiere();
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
                $lastEc = $ecOrdre->getOrdreSuivant($ue);
                $elementConstitutif->setTexteEcLibre($request->request->get('ficheMatiereLibre'));
                $elementConstitutif->setLibelle($request->request->get('ficheMatiereLibreLibelle'));
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
            }

            return $this->json(true);
        }

        return $this->render('element_constitutif/new.html.twig', [
            'elementConstitutif' => $elementConstitutif,
            'form' => $form->createView(),
            'ue' => $ue,
            'parcours' => $parcours,
            'isAdmin' => $isAdmin,
        ]);
    }

    #[Route('/new-enfant/{ue}/{parcours}', name: 'app_element_constitutif_new_enfant', methods: ['GET', 'POST'])]
    public function newEnfant(
        NatureUeEcRepository         $natureUeEcRepository,
        TypeEcRepository         $typeEcRepository,
        EcOrdre                      $ecOrdre,
        Request                      $request,
        FicheMatiereRepository       $ficheMatiereRepository,
        ElementConstitutifRepository $elementConstitutifRepository,
        Ue                           $ue,
        Parcours                     $parcours
    ): Response {
        $ecParent = $elementConstitutifRepository->find($request->query->get('element'));

        if ($ecParent === null) {
            throw new RuntimeException('Element constitutif parent non trouvé');
        }

        $elementConstitutif = new ElementConstitutif();
        $elementConstitutif->setParcours($parcours);

        $elementConstitutif->setModaliteEnseignement($parcours?->getModalitesEnseignement());
        $elementConstitutif->setUe($ue);
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplôme non trouvé');
        }

        $form = $this->createForm(ElementConstitutifEnfantType::class, $elementConstitutif, [
            'action' => $this->generateUrl(
                'app_element_constitutif_new_enfant',
                ['ue' => $ue->getId(), 'parcours' => $parcours->getId(), 'element' => $request->query->get('element')]
            ),
            'typeDiplome' => $typeDiplome,
            'formation' => $parcours->getFormation(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //            $typeEc = $typeEcRepository->find($request->request->get('typeEc'));
            //            $elementConstitutif->setTypeEc($typeEc);

            $natureEc = $natureUeEcRepository->findOneBy(['choix' => false, 'libre' => false, 'type' => 'ec']);
            $elementConstitutif->setNatureUeEc($natureEc);

            if ($form->get('typeEcTexte')->getData() !== null && $form->get('typeEc')->getData() === null) {
                $tu = new TypeEc();
                $tu->setLibelle($form->get('typeEcTexte')->getData());
                $tu->addTypeDiplome($typeDiplome);
                $tu->setFormation($parcours->getFormation());
                $typeEcRepository->save($tu, true);
                $elementConstitutif->setTypeEc($tu);
            }

            if (str_starts_with($request->request->get('ficheMatiere'), 'id_')) {
                $ficheMatiere = $ficheMatiereRepository->find((int)str_replace(
                    'id_',
                    '',
                    $request->request->get('ficheMatiere')
                ));
            } else {
                $ficheMatiere = new FicheMatiere();
                $ficheMatiere->setLibelle($request->request->get('ficheMatiereLibelle'));
                $ficheMatiere->setParcours($parcours);
                $ficheMatiereRepository->save($ficheMatiere, true);
            }

            $elementConstitutif->setFicheMatiere($ficheMatiere);
            $elementConstitutif->setEcParent($ecParent->getEcParent());
            //si up => $ecParent->getOrdre() + 1; si down => $ecParent->getOrdre()
            if ($request->query->get('sens', 'after') === 'avant') {
                $ordre = $ecParent->getOrdre();
                $ecOrdre->decalerEnfant($ecParent->getEcParent(), $ordre);
                $elementConstitutif->setOrdre($ordre);
            } else {
                $ordre = $ecParent->getOrdre() + 1;
                $ecOrdre->decalerEnfant($ecParent->getEcParent(), $ordre);
                $elementConstitutif->setOrdre($ordre);
            }


            $elementConstitutif->genereCode();

            $elementConstitutifRepository->save($elementConstitutif, true);

            return $this->json(true);
        }


        return $this->render('element_constitutif/new_enfant.html.twig', [
            'element_constitutif' => $elementConstitutif,
            'form' => $form->createView(),
            'ue' => $ue,
            'parcours' => $parcours,
            'typeEcs' => $typeEcRepository->findByTypeDiplomeAndFormation($typeDiplome, $parcours->getFormation()),
            'matieres' => $ficheMatiereRepository->findByParcours($parcours)
        ]);
    }

    #[Route('/{id}/edit/{parcours}', name: 'app_element_constitutif_edit', methods: ['GET', 'POST'])]
    public function edit(
        EcOrdre                      $ecOrdre,
        FicheMatiereRepository       $ficheMatiereRepository,
        EntityManagerInterface       $entityManager,
        Request                      $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        TypeEcRepository             $typeEcRepository,
        ElementConstitutif           $elementConstitutif,
        Parcours                     $parcours
    ): Response {
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplôme non trouvé');
        }

        $isAdmin = $this->isGranted('ROLE_SES');
        $isBut = $parcours->getFormation()?->getTypeDiplome()?->getLibelleCourt() === 'BUT';
        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl(
                'app_element_constitutif_edit',
                ['id' => $elementConstitutif->getId(), 'parcours' => $parcours->getId()]
            ),
            'typeDiplome' => $typeDiplome,
            'formation' => $parcours->getFormation(),
            'isAdmin' => $isAdmin || $isBut
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('typeEcTexte')->getData() !== null && $form->get('typeEc')->getData() === null) {
                $tu = new TypeEc();
                $tu->setLibelle($form->get('typeEcTexte')->getData());
                $tu->addTypeDiplome($typeDiplome);
                $typeEcRepository->save($tu, true);
                $elementConstitutif->setTypeEc($tu);
            }

            $uow = $entityManager->getUnitOfWork();
            $uow->computeChangeSets();
            $changeSet = $uow->getEntityChangeSet($elementConstitutif);

            if (isset($changeSet['natureUeEc'])) {
                //die('changement');
                //initial $changeSet['natureUeEc'][0]
                if ($changeSet['natureUeEc'][0]->isChoix() === true) {
                    // c'était une EC à choix, on supprime les choix.
                    foreach ($elementConstitutif->getEcEnfants() as $ecEnfant) {
                        $elementConstitutif->removeEcEnfant($ecEnfant);
                        $entityManager->remove($ecEnfant);
                    }
                    $entityManager->flush();
                }

                if ($changeSet['natureUeEc'][0]->isLibre() === true) {
                    // c'était une EC libre, on efface le commentaire
                    $elementConstitutif->setTexteEcLibre(null);
                }
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
                    $ficheMatiere->setLibelle($request->request->get('ficheMatiereLibelle'));
                    $ficheMatiere->setParcours($parcours); //todo: ajouter le semestre
                    $ficheMatiereRepository->save($ficheMatiere, true);
                }
                $elementConstitutif->setFicheMatiere($ficheMatiere);
            } elseif ($elementConstitutif->getNatureUeEc()?->isLibre() === true) {
                $elementConstitutif->setTexteEcLibre($request->request->get('ficheMatiereLibre'));
                $elementConstitutif->setLibelle($request->request->get('ficheMatiereLibreLibelle'));
                //todo: supprimer les EC enfants le cas échéant.
                //todo: supprimer MCCC...

            } elseif ($elementConstitutif->getNatureUeEc()?->isChoix() === true) {
                $elementConstitutif->setLibelle($request->request->get('ficheMatiereLibre'));
                $elementConstitutif->setFicheMatiere(null);
                //on récupère le champs matières, on découpe selon la ,. Si ca commence par "id_", on récupère la matière, sinon on créé la matière
                $matieres = explode(',', $request->request->get('matieres'));
                foreach ($matieres as $matiere) {
                    //on vérifie si pas déjà existant
                    if (str_starts_with($matiere, 'id_')) {
                        $ficheMatiere = $ficheMatiereRepository->find((int)str_replace('id_', '', $matiere));
                    } else {
                        $ficheMatiere = new FicheMatiere();
                        $ficheMatiere->setLibelle(str_replace('ac_', '', $matiere));
                        $ficheMatiere->setParcours($parcours); //todo: ajouter le semestre
                        $ficheMatiereRepository->save($ficheMatiere, true);
                    }

                    $existe = $elementConstitutifRepository->findOneBy([
                        'ficheMatiere' => $ficheMatiere,
                        'ecParent' => $elementConstitutif
                    ]);

                    if (!$existe) {
                        //sinon on ajoute
                        $ec = new ElementConstitutif();
                        $nextSousEc = $ecOrdre->getOrdreEnfantSuivant($elementConstitutif);
                        $ec->setEcParent($elementConstitutif);
                        $ec->setParcours($parcours);
                        $ec->setModaliteEnseignement($parcours?->getModalitesEnseignement());
                        $ec->setUe($elementConstitutif->getUe());
                        $ec->setNatureUeEc($elementConstitutif->getNatureUeEc());
                        $ec->setFicheMatiere($ficheMatiere);
                        $ec->setOrdre($nextSousEc);
                        $ec->genereCode();
                        $elementConstitutifRepository->save($ec, true);
                    }
                }
            }

            $elementConstitutif->genereCode();
            $entityManager->flush();

            return $this->json(true);
        }

        return $this->render('element_constitutif/edit.html.twig', [
            'elementConstitutif' => $elementConstitutif,
            'form' => $form->createView(),
            'ue' => $elementConstitutif->getUe(),
            'parcours' => $parcours,
            'isAdmin' => $isAdmin,
            'isBut' => $isBut
        ]);
    }

    #[
        Route('/{id}/edit-enfant/{parcours}/{ue}', name: 'app_element_constitutif_edit_enfant', methods: [
            'GET',
            'POST'
        ])]
    public function editEnfant(
        TypeEcRepository             $typeEcRepository,
        NatureUeEcRepository             $natureUeEcRepository,
        FicheMatiereRepository       $ficheMatiereRepository,
        Request                      $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif           $elementConstitutif,
        Parcours                     $parcours,
        Ue                           $ue
    ): Response {
        $isAdmin = $this->isGranted('ROLE_SES');
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if ($request->isMethod('POST')) {
            if ($request->request->has('ficheMatiereLibre') && $request->request->get('ficheMatiereLibre') !== '') {
                $natureEc = $natureUeEcRepository->find(9);
                $elementConstitutif->setNatureUeEc($natureEc);
                $elementConstitutif->setTexteEcLibre($request->request->get('ficheMatiereLibre'));
                $elementConstitutif->setLibelle($request->request->get('ficheMatiereLibreLibelle'));
                $elementConstitutif->setFicheMatiere(null);
            } else {
                if (str_starts_with($request->request->get('ficheMatiere'), 'id_')) {
                    $ficheMatiere = $ficheMatiereRepository->find((int)str_replace(
                        'id_',
                        '',
                        $request->request->get('ficheMatiere')
                    ));
                } else {
                    $ficheMatiere = new FicheMatiere();
                    $ficheMatiere->setLibelle($request->request->get('ficheMatiereLibelle'));
                    $ficheMatiere->setParcours($parcours); //todo: ajouter le semestre
                    $ficheMatiereRepository->save($ficheMatiere, true);
                }
                $elementConstitutif->setFicheMatiere($ficheMatiere);
            }

            $typeEc = $typeEcRepository->find($request->request->get('typeEc'));
            $elementConstitutif->setTypeEc($typeEc);

            $elementConstitutifRepository->save($elementConstitutif, true);

            return $this->json(true);
        }


        return $this->render('element_constitutif/_editEnfant.html.twig', [
            'element_constitutif' => $elementConstitutif,
            'parcours' => $parcours,
            'ue' => $ue,
            'typeEcs' => $typeEcRepository->findByTypeDiplomeAndFormation($typeDiplome, $parcours->getFormation()),
            'matieres' => $ficheMatiereRepository->findByParcours($parcours),
            'isAdmin' => $isAdmin
        ]);
    }

    #[Route('/{id}/structure-ec/{parcours}', name: 'app_element_constitutif_structure', methods: ['GET', 'POST'])]
    public function structureEc(
        Request                      $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif           $elementConstitutif,
        Parcours                     $parcours
    ): Response {
        //        if ($this->isGranted(
        //            'ROLE_FORMATION_EDIT_MY',
        //            $elementConstitutif->getParcours()->getFormation()
        //        )) { //todo: ajouter le workflow...

        $isParcoursProprietaire = $elementConstitutif->getFicheMatiere()?->getParcours()?->getId() === $parcours->getId();

        //todo: deprecated $raccroche ?
        $raccroche = $elementConstitutif->getFicheMatiere()?->getParcours()?->getId() !== $parcours->getId();
        $getElement = new GetElementConstitutif($elementConstitutif, $parcours);
        $ecHeures = $getElement->getFicheMatiereHeures();

        $form = $this->createForm(EcStep4Type::class, $ecHeures, [
            'isModal' => true,
            'data_class' => $ecHeures::class,
            'modalite' => $parcours->getModalitesEnseignement(),
            'action' => $this->generateUrl(
                'app_element_constitutif_structure',
                ['id' => $elementConstitutif->getId(), 'parcours' => $parcours->getId()]
            ),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() &&
            ($elementConstitutif->isSynchroHeures() === false ||
            $elementConstitutif->getParcours()?->getId() === $parcours->getId())
        ) {
            if (array_key_exists('heuresEnfantsIdentiques', $request->request->all()['ec_step4'])) {
                if ($elementConstitutif->getEcParent() !== null) {
                    $elementConstitutif->getEcParent()->setHeuresEnfantsIdentiques((bool)$request->request->all()['ec_step4']['heuresEnfantsIdentiques']);
                } else {
                    $elementConstitutif->setHeuresEnfantsIdentiques((bool)$request->request->all()['ec_step4']['heuresEnfantsIdentiques']);
                }
            } else {
                $elementConstitutif->setHeuresEnfantsIdentiques(false);
            }

            $elementConstitutif->setSansHeure((bool)$request->request->get('sansHeure'));
            $elementConstitutifRepository->save($elementConstitutif, true);

            return $this->json(true);
        }

        return $this->render('element_constitutif/_structureEcModal.html.twig', [
            'ec' => $elementConstitutif,
            'form' => $form->createView(),
            'raccroche' => $raccroche,
            'parcours' => $parcours,
            'isParcoursProprietaire' => $isParcoursProprietaire,
            'modalite' => $parcours->getModalitesEnseignement()
        ]);
    }

    #[Route('/{id}/structure-ec-non-editable', name: 'app_element_constitutif_structure_non_editable', methods: ['GET', 'POST'])]
    public function structureEcNonEditable(
        ElementConstitutif           $elementConstitutif,
    ): Response {
        return $this->render('element_constitutif/_structureEcNonEditable.html.twig', [
            'ec' => $elementConstitutif,
        ]);
    }

    #[Route('/{id}/structure-but', name: 'app_element_constitutif_structure_but', methods: ['GET', 'POST'])]
    public function structureBut(
        Request                $request,
        FicheMatiereRepository $ficheMatiereRepository,
        FicheMatiere           $ficheMatiere
    ): Response {
        $form = $this->createForm(FicheMatiereStep4Type::class, $ficheMatiere, [
            'action' => $this->generateUrl(
                'app_element_constitutif_structure_but',
                ['id' => $ficheMatiere->getId()]
            ),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $ficheMatiereRepository->save($ficheMatiere, true);

            return $this->json(true);
        }

        return $this->render('element_constitutif/_structureEcModalBut.html.twig', [
            'ficheMatiere' => $ficheMatiere,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/{ue}/deplacer/{sens}', name: 'app_element_constitutif_deplacer', methods: ['GET'])]
    public function deplacer(
        EcOrdre            $ecOrdre,
        ElementConstitutif $elementConstitutif,
        Ue                 $ue,
        string             $sens
    ): Response {
        $ecOrdre->deplacerElementConstitutif($elementConstitutif, $sens, $ue);

        return $this->json(true);
    }

    #[Route('/{id}', name: 'app_element_constitutif_delete', methods: ['DELETE'])]
    public function delete(
        EcOrdre                      $ecOrdre,
        EntityManagerInterface       $entityManager,
        Request                      $request,
        ElementConstitutif           $elementConstitutif,
        ElementConstitutifRepository $elementConstitutifRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $elementConstitutif->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            foreach ($elementConstitutif->getMcccs() as $mccc) {
                $elementConstitutif->removeMccc($mccc);
                $entityManager->remove($mccc);
            }

            foreach ($elementConstitutif->getEcEnfants() as $enfant) {
                $entityManager->remove($enfant);
            }
            if ($elementConstitutif->getEcParent() === null) {
                $ecOrdre->removeElementConstitutif($elementConstitutif);
            } else {
                $ecOrdre->removeElementConstitutifEnfant($elementConstitutif);
            }
            $entityManager->remove($elementConstitutif);
            $entityManager->flush();


            return $this->json(true);
        }

        return $this->json(false, 500);
    }

}
