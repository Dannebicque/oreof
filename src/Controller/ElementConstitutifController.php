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
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\TypeEc;
use App\Entity\Ue;
use App\Form\EcStep4Type;
use App\Form\ElementConstitutifEnfantType;
use App\Form\ElementConstitutifType;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use App\Repository\NatureUeEcRepository;
use App\Repository\TypeEcRepository;
use App\Repository\TypeEpreuveRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
            $matieres = $ficheMatiereRepository->findByParcours($parcours);


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
        $elementConstitutif = new ElementConstitutif();
        $elementConstitutif->setFicheMatiere(null);
        $elementConstitutif->setParcours($parcours);

        $elementConstitutif->setModaliteEnseignement($parcours?->getModalitesEnseignement());
        $elementConstitutif->setUe($ue);
        $typeDiplome = $parcours->getFormation()->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplôme non trouvé');
        }

        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl(
                'app_element_constitutif_new',
                ['ue' => $ue->getId(), 'parcours' => $parcours->getId()]
            ),
            'typeDiplome' => $typeDiplome,
            'formation' => $parcours->getFormation(),
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
                $elementConstitutif->setOrdre($lastEc);
                $elementConstitutif->genereCode();
                $elementConstitutifRepository->save($elementConstitutif, true);
            }

            if ($elementConstitutif->getMcccs()->count() === 0) {
                $typeD->initMcccs($elementConstitutif);
            }

            return $this->json(true);
        }

        return $this->render('element_constitutif/new.html.twig', [
            'elementConstitutif' => $elementConstitutif,
            'form' => $form->createView(),
            'ue' => $ue,
            'parcours' => $parcours,
        ]);
    }

    #[Route('/new-enfant/{ue}/{parcours}', name: 'app_element_constitutif_new_enfant', methods: ['GET', 'POST'])]
    public function newEnfant(
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
            )
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        $form = $this->createForm(ElementConstitutifType::class, $elementConstitutif, [
            'action' => $this->generateUrl(
                'app_element_constitutif_edit',
                ['id' => $elementConstitutif->getId(), 'parcours' => $parcours->getId()]
            ),
            'typeDiplome' => $typeDiplome,
            'formation' => $parcours->getFormation()
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
            } else {
                $elementConstitutif->setTexteEcLibre($request->request->get('ficheMatiereLibre'));
            }

            $elementConstitutif->genereCode();
            $elementConstitutifRepository->save($elementConstitutif, true);

            return $this->json(true);
        }

        return $this->render('element_constitutif/edit.html.twig', [
            'elementConstitutif' => $elementConstitutif,
            'form' => $form->createView(),
            'ue' => $elementConstitutif->getUe(),
            'parcours' => $parcours,
        ]);
    }

    #[
        Route('/{id}/edit-enfant/{parcours}/{ue}', name: 'app_element_constitutif_edit_enfant', methods: [
            'GET',
            'POST'
        ])]
    public function editEnfant(
        FicheMatiereRepository       $ficheMatiereRepository,
        Request                      $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif           $elementConstitutif,
        Parcours                     $parcours,
        Ue                           $ue
    ): Response {
//        $form = $this->createForm(ElementConstitutifEnfantType::class, $elementConstitutif, [
//            'action' => $this->generateUrl(
        //                'app_element_constitutif_edit_enfant',
//                ['id' => $elementConstitutif->getId(), 'parcours' => $parcours->getId(), 'ue' => $ue->getId()]
//            )
//        ]);
//        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
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

            $elementConstitutifRepository->save($elementConstitutif, true);

            return $this->json(true);
        }


        return $this->render('element_constitutif/_editEnfant.html.twig', [
            'element_constitutif' => $elementConstitutif,
            'parcours' => $parcours,
            'ue' => $ue,
            'matieres' => $ficheMatiereRepository->findByParcours($parcours)
        ]);
    }

    #[Route('/{id}/structure-ec', name: 'app_element_constitutif_structure', methods: ['GET', 'POST'])]
    public function structureEc(
        Request                      $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif           $elementConstitutif
    ): Response {
//        if ($this->isGranted(
//            'ROLE_FORMATION_EDIT_MY',
//            $elementConstitutif->getParcours()->getFormation()
//        )) { //todo: ajouter le workflow...
        $form = $this->createForm(EcStep4Type::class, $elementConstitutif, [
            'isModal' => true,
            'action' => $this->generateUrl(
                'app_element_constitutif_structure',
                ['id' => $elementConstitutif->getId()]
            ),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (array_key_exists('heuresEnfantsIdentiques', $request->request->all()['ec_step4'])) {
                if ($elementConstitutif->getEcParent() !== null) {
                    $elementConstitutif->getEcParent()->setHeuresEnfantsIdentiques((bool)$request->request->all()['ec_step4']['heuresEnfantsIdentiques']);
                } else {
                    $elementConstitutif->setHeuresEnfantsIdentiques((bool)$request->request->all()['ec_step4']['heuresEnfantsIdentiques']);
                }
            } else {
                $elementConstitutif->setHeuresEnfantsIdentiques(false);
            }

            $elementConstitutifRepository->save($elementConstitutif, true);

            return $this->json(true);
        }

        return $this->render('element_constitutif/_structureEcModal.html.twig', [
            'ec' => $elementConstitutif,
            'form' => $form->createView(),
        ]);
        // }
//
//        return $this->render('element_constitutif/_structureEcNonEditable.html.twig', [
//            'ec' => $elementConstitutif,
//        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[Route('/{id}/mccc-ec', name: 'app_element_constitutif_mccc', methods: ['GET', 'POST'])]
    public function mcccEc(
        TypeDiplomeRegistry          $typeDiplomeRegistry,
        TypeEpreuveRepository        $typeEpreuveRepository,
        Request                      $request,
        ElementConstitutifRepository $elementConstitutifRepository,
        ElementConstitutif           $elementConstitutif
    ): Response {
        $formation = $elementConstitutif->getParcours()?->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $formation->getTypeDiplome();
        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplome non trouvé');
        }
        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

        if ($this->isGranted('CAN_FORMATION_EDIT_MY', $formation) ||
            $this->isGranted('CAN_PARCOURS_EDIT_MY', $elementConstitutif->getParcours())) { //todo: ajouter le workflow...
            if ($request->isMethod('POST')) {
                if ($request->request->has('ec_step4') && array_key_exists('ects', $request->request->all()['ec_step4'])) {
                    $elementConstitutif->setEcts((float)$request->request->all()['ec_step4']['ects']);
                } else {
                    $elementConstitutif->setEcts($elementConstitutif->getEcParent()?->getEcts());
                }

                if ($request->request->has('ec_step4') && array_key_exists('quitus', $request->request->all()['ec_step4'])) {
                    $elementConstitutif->setQuitus((bool)$request->request->all()['ec_step4']['quitus']);
                } else {
                    $elementConstitutif->setQuitus(false);
                }

                if ($request->request->has('ec_step4') && array_key_exists('mcccEnfantsIdentique', $request->request->all()['ec_step4'])) {
                    if ($elementConstitutif->getEcParent() !== null) {
                        $elementConstitutif->getEcParent()->setMcccEnfantsIdentique((bool)$request->request->all()['ec_step4']['mcccEnfantsIdentique']);
                    } else {
                        $elementConstitutif->setMcccEnfantsIdentique((bool)$request->request->all()['ec_step4']['mcccEnfantsIdentique']);
                    }
                } else {
                    $elementConstitutif->setMcccEnfantsIdentique(false);
                }

                if ($request->request->get('choix_type_mccc') !== $elementConstitutif->getTypeMccc()) {
                    $elementConstitutif->setTypeMccc($request->request->get('choix_type_mccc'));
                    $elementConstitutifRepository->save($elementConstitutif, true);
                    $typeD->clearMcccs($elementConstitutif);
                    $typeD->initMcccs($elementConstitutif);
                }

                $typeD->saveMcccs($elementConstitutif, $request->request);

                return $this->json(true);
            }

            return $this->render('element_constitutif/_mcccEcModal.html.twig', [
                'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
                'ec' => $elementConstitutif,
                'templateForm' => $typeD::TEMPLATE_FORM_MCCC,
                'mcccs' => $typeD->getMcccs($elementConstitutif),
                'wizard' => false
            ]);
        }

        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', [
            'ec' => $elementConstitutif,
            'templateForm' => $typeD::TEMPLATE_FORM_MCCC,
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    public function displayMcccEc(
        TypeDiplomeRegistry   $typeDiplomeRegistry,
        TypeEpreuveRepository $typeEpreuveRepository,
        ElementConstitutif    $elementConstitutif
    ): Response {
        $formation = $elementConstitutif->getParcours()->getFormation();
        if ($formation === null) {
            throw new RuntimeException('Formation non trouvée');
        }
        $typeDiplome = $formation->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new RuntimeException('Type de diplome non trouvé');
        }

        $typeD = $typeDiplomeRegistry->getTypeDiplome($typeDiplome->getModeleMcc());

        return $this->render('element_constitutif/_mcccEcNonEditable.html.twig', [
            'ec' => $elementConstitutif,
            'typeEpreuves' => $typeEpreuveRepository->findByTypeDiplome($typeDiplome),
            'templateForm' => $typeD::TEMPLATE_FORM_MCCC,
            'mcccs' => $typeD->getMcccs($elementConstitutif),
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
