<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Structure/UeController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Structure;

use App\Classes\JsonReponse;
use App\Classes\UeOrdre;
use App\Entity\FicheMatiereMutualisable;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreParcours;
use App\Entity\TypeUe;
use App\Entity\Ue;
use App\Entity\UeMutualisable;
use App\Form\UeType;
use App\Repository\ComposanteRepository;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereMutualisableRepository;
use App\Repository\FormationRepository;
use App\Repository\NatureUeEcRepository;
use App\Repository\ParcoursRepository;
use App\Repository\SemestreParcoursRepository;
use App\Repository\SemestreRepository;
use App\Repository\TypeUeRepository;
use App\Repository\UeMutualisableRepository;
use App\Repository\UeRepository;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/ue', name: 'structure_ue_')
]
class UeController extends AbstractController
{
    #[
        Route('/detail/semestre/{semestre}/{parcours}', name: 'detail_semestre')
    ]
    public function detailComposante(
        Request              $request,
        UeRepository         $ueRepository,
        TypeUeRepository     $typeUeRepository,
        NatureUeEcRepository $natureUeEcRepository,
        Semestre             $semestre,
        Parcours             $parcours,
    ): Response {
        $raccroche = (bool)$request->query->get('raccroche', false);
        $ues = $ueRepository->findBy(['semestre' => $semestre], ['ordre' => 'ASC']);
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        return $this->render('structure/ue/_liste.html.twig', [
            'ues' => $ues,
            'raccroche' => $raccroche,
            'semestre' => $semestre,
            'typeUes' => $typeDiplome?->getTypeUes(),
            'natureUeEcs' => $natureUeEcRepository->findAll(),
            'parcours' => $parcours,
            'typeDiplome' => $typeDiplome,
        ]);
    }

    /**
     * @throws \App\TypeDiplome\Exceptions\TypeDiplomeNotFoundException
     */
    #[
        Route('/add-ue/semestre/{semestre}/{parcours}', name: 'add_ue_semestre')
    ]
    public function addUe(
        UeOrdre              $ueOrdre,
        NatureUeEcRepository $natureUeEcRepository,
        TypeUeRepository     $typeUeRepository,
        Request              $request,
        UeRepository         $ueRepository,
        Semestre             $semestre,
        Parcours             $parcours
    ): Response {
        $ue = new Ue();
        $ueOrigine = $request->query->get('ue', null);
        if ($ueOrigine !== null) {
            $ueOrigine = $ueRepository->find($ueOrigine);
        }
        $sens = $request->query->get('sens', 'after');

        $ue->setSemestre($semestre);
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplôme non trouvé');
        }
        $form = $this->createForm(UeType::class, $ue, [
            'action' => $this->generateUrl('structure_ue_add_ue_semestre', [
                'semestre' => $semestre->getId(),
                'parcours' => $parcours->getId(),
                'ue' => $ueOrigine ? $ueOrigine->getId() : null,
            ]),
            'typeDiplome' => $typeDiplome,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($ueOrigine !== null && $ueOrigine->getUeParent() !== null) {
                $ue->setUeParent($ueOrigine->getUeParent());
                if ($sens === 'after') {
                    $ordreInit = $ueOrigine->getOrdre();
                    $ueOrdre->decaleSousOrdre($ueOrigine->getUeParent(), $ueOrigine->getOrdre(), $ue);
                    $ue->setOrdre($ordreInit);
                } else {
                    $ordreInit = $ueOrigine->getOrdre();
                    $ueOrdre->decaleSousOrdre($ueOrigine->getUeParent(), $ueOrigine->getOrdre(), $ue);
                    $ue->setOrdre($ordreInit);
                }
            } else {
                $ordre = $ueRepository->getMaxOrdre($semestre);
                $ue->setOrdre($ordre + 1);
            }

            if ($form->get('typeUeTexte')->getData() !== null && $form->get('typeUe')->getData() === null) {
                $tu = new TypeUe();
                $tu->setLibelle($form->get('typeUeTexte')->getData());
                $tu->addTypeDiplome($typeDiplome);
                $typeUeRepository->save($tu, true);
                $ue->setTypeUe($tu);
            }
            $ueRepository->save($ue, true);

            if ($ue->getNatureUeEc()?->isChoix() === true) {
                //on ajoute par défaut deux UE enfants
                $ueEnfant1 = new Ue();
                $ueEnfant1->setSemestre($semestre);
                $ueEnfant1->setNatureUeEc($ue->getNatureUeEc());
                $ueEnfant1->setOrdre(1);
                $ueEnfant1->setUeParent($ue);
                $ueEnfant1->setLibelle('Choix 1');
                $ueRepository->save($ueEnfant1, true);

                $ueEnfant2 = new Ue();
                $ueEnfant2->setSemestre($semestre);
                $ueEnfant2->setNatureUeEc($ue->getNatureUeEc());
                $ueEnfant2->setOrdre(2);
                $ueEnfant2->setUeParent($ue);
                $ueEnfant2->setLibelle('Choix 2');
                $ueRepository->save($ueEnfant2, true);
            }


            return $this->json(true);
        }

        return $this->render('structure/ue/_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/ue/update/typeUe/{ue}', name: 'change_type_ue', methods: ['POST'])]
    public function updateTypeUe(
        Request          $request,
        UeRepository     $ueRepository,
        TypeUeRepository $typeUeRepository,
        Ue               $ue,
    ): Response {
        $typeUe = JsonRequest::getValueFromRequest($request, 'value');

        if ($typeUe !== '') {
            $typeUe = $typeUeRepository->find($typeUe);
            $ue->setTypeUe($typeUe);
        } else {
            $ue->setTypeUe(null);
        }

        $ueRepository->save($ue, true);

        return $this->json(true);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/ue/update/nature/{ue}', name: 'change_nature_ue', methods: ['POST'])]
    public function updateNatureUe(
        Request              $request,
        UeRepository         $ueRepository,
        NatureUeEcRepository $natureUeEcRepository,
        Ue                   $ue,
    ): Response {
        $idNatureUe = JsonRequest::getValueFromRequest($request, 'value');
        if ($idNatureUe !== '') {
            $natureUe = $natureUeEcRepository->find($idNatureUe);
            if ($natureUe !== null) {
                $ue->setNatureUeEc($natureUe);
                if ($natureUe->isChoix() === true) {
                    $ue->setOrdre(1);
                    $ue2 = clone $ue;
                    $ue2->setOrdre(2);
                    $ueRepository->save($ue2, true);
                }
            }
        } else {
            $ue->setNatureUeEc(null);//todo: gérer ??
        }

        $ueRepository->save($ue, true);

        return $this->json(true);
    }

    #[Route('/deplacer/{ue}/{sens}', name: 'deplacer', methods: ['GET'])]
    public function deplacer(
        UeOrdre $ueOrdre,
        Ue      $ue,
        string  $sens
    ): Response {
        $ueOrdre->deplacerUe($ue, $sens);

        return $this->json(true);
    }

    #[Route('/deplacer_sub/{ue}/{sens}', name: 'deplacer_sub', methods: ['GET'])]
    public function deplacerSub(
        UeOrdre $ueOrdre,
        Ue      $ue,
        string  $sens
    ): Response {
        $ueOrdre->deplacerSubUe($ue, $sens);

        return $this->json(true);
    }

    #[Route('/{id}/{parcours}/modifier', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        NatureUeEcRepository $natureUeEcRepository,
        TypeUeRepository     $typeUeRepository,
        Request              $request,
        Ue                   $ue,
        Parcours             $parcours,
        UeRepository         $ueRepository
    ): Response {
        $typeDiplome = $parcours->getFormation()?->getTypeDiplome();

        if ($typeDiplome === null) {
            throw new \Exception('Type de diplôme non trouvé');
        }
        $form = $this->createForm(UeType::class, $ue, [
            'action' => $this->generateUrl('structure_ue_edit', [
                'id' => $ue->getId(),
                'parcours' => $parcours->getId()
            ]),
            'typeDiplome' => $typeDiplome,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('typeUeTexte')->getData() !== null && $form->get('typeUe')->getData() === null) {
                $tu = new TypeUe();
                $tu->setLibelle($form->get('typeUeTexte')->getData());
                $tu->addTypeDiplome($typeDiplome);
                $typeUeRepository->save($tu, true);
                $ue->setTypeUe($tu);
            }

            if ($form->get('natureUeEc')->getData() !== null) {
                if ($form->get('natureUeEc')->getData()->isChoix() === true) {
                    $ue1 = clone $ue;
                    $ue1->setUeParent($ue);
                    $ue1->setOrdre(1);
                    $ueRepository->save($ue1, true);

                    $ue2 = clone $ue;
                    $ue2->setUeParent($ue);
                    $ue2->setOrdre(2);
                    $ueRepository->save($ue2, true);
                } else {
                    foreach ($ue->getUeEnfants() as $ueEnfant) {
                        $ueRepository->remove($ueEnfant, true);
                    }
                }
            }

            $ueRepository->save($ue, true);
            return $this->json(true);
        }

        return $this->render('structure/ue/_new.html.twig', [
            'form' => $form->createView(),
            'ue' => $ue,
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        UeOrdre                      $ueOrdre,
        Request                      $request,
        Ue                           $ue,
        ElementConstitutifRepository $elementConstitutifRepository,
        UeRepository                 $ueRepository
    ): Response {
        if ($this->isCsrfTokenValid(
            'delete' . $ue->getId(),
            JsonRequest::getValueFromRequest($request, 'csrf')
        )) {
            //todo: gérer les UE mutualisées
            foreach ($ue->getElementConstitutifs() as $ec) {
                $ue->removeElementConstitutif($ec);
                foreach ($ec->getEcEnfants() as $ecEnfant) {
                    $ue->removeElementConstitutif($ecEnfant);
                    $elementConstitutifRepository->remove($ecEnfant, true);
                }
                $elementConstitutifRepository->remove($ec, true);
            }

            $ueRepository->remove($ue, true);

            if ($ue->getUeParent() !== null) {
                $ueOrdre->renumeroterSubUE($ue->getOrdre(), $ue->getUeParent(), $ue->getSemestre());
            } else {
                $ueOrdre->renumeroterUE($ue->getOrdre(), $ue->getSemestre());
            }

            return $this->json(true);
        }

        return $this->json(false);
    }

    #[
        Route('/mutualiser/{ue}/{semestre}', name: 'mutualiser')
    ]
    public function mutualiser(
        ComposanteRepository $composanteRepository,
        Ue                   $ue,
        Semestre             $semestre
    ): Response {
        return $this->render('structure/ue/_mutualiser.html.twig', [
            'semestre' => $semestre,
            'ue' => $ue,
            'composantes' => $composanteRepository->findAll()
        ]);
    }

    #[Route('/{ue}/mutualise/ajax', name: 'mutualise_add_ajax', methods: [
        'POST',
        'DELETE'
    ])]
    public function mutualiseAjax(
        EntityManagerInterface   $entityManager,
        Request                  $request,
        FormationRepository      $formationRepository,
        ParcoursRepository       $parcoursRepository,
        UeMutualisableRepository $ueMutualisableRepository,
        Ue                       $ue,
    ): Response {
        $data = JsonRequest::getFromRequest($request);
        $t = [];
        switch ($data['field']) {
            case 'decrocher':
                $ue->setUeRaccrochee(null);
                $entityManager->flush();
                return $this->json(true);
            case 'raccrocher':
                $uem = $ueMutualisableRepository->find($data['value']);
                if ($uem !== null) {
                    $ue->setUeRaccrochee($uem);
                    $entityManager->flush();
                    return $this->json(true);
                }

                return $this->json(
                    ['error' => 'UE non trouvée'],
                    500
                );
            case 'liste':
                return $this->render('structure/ue/_liste_mutualise.html.twig', [
                    'ues' => $ueMutualisableRepository->findBy(['ue' => $ue]),
                    'ue' => $ue
                ]);
            case 'formation':
                $formations = $formationRepository->findBy(['composantePorteuse' => $data['value']]);
                foreach ($formations as $formation) {
                    $t[] = [
                        'id' => $formation->getId(),
                        'libelle' => $formation->getDisplay()
                    ];
                }
                break;
            case 'parcours':
                $parcours = $parcoursRepository->findBy(['formation' => $data['value']]);
                foreach ($parcours as $parcour) {
                    $t[] = [
                        'id' => $parcour->getId(),
                        'libelle' => $parcour->getLibelle()
                    ];
                }
                break;
            case 'save':
                $parcours = $parcoursRepository->find($data['parcours']);
                $exist = $ueMutualisableRepository->findOneBy([
                    'parcours' => $parcours,
                    'ue' => $ue
                ]);

                if ($exist === null) {
                    $ueMutualise = new UeMutualisable();
                    $ueMutualise->setUe($ue);
                    $ueMutualise->setParcours($parcours);
                    $entityManager->persist($ueMutualise);
                    $entityManager->flush();
                }

                return $this->json(true);
            case 'delete':
                $sem = $ueMutualisableRepository->find($data['ue']);
                //todo: vérifier si pas utilisé

                if ($sem !== null) {
                    $entityManager->remove($sem);
                    $entityManager->flush();
                }

                return $this->json(true);
        }

        return $this->json($t);
    }

    #[
        Route('/raccrocher/{ue}/{parcours}', name: 'raccrocher')
    ]
    public function raccrocher(
        UeMutualisableRepository $ueMutualisableRepository,
        Parcours                 $parcours,
        Ue                       $ue
    ): Response {
        $ues = $ueMutualisableRepository->findBy(['parcours' => $parcours]);


        return $this->render('structure/ue/_raccrocher.html.twig', [
            'ue' => $ue,
            'parcours' => $parcours,
            'ues' => $ues
        ]);
    }

    #[
        Route('/dupliquer/{ue}/{parcours}', name: 'dupliquer')
    ]
    public function dupliquer(
        Ue       $ue,
        Parcours $parcours
    ): Response {
        return $this->render('structure/ue/_dupliquer.html.twig', [
            'ue' => $ue,
            'parcours' => $parcours,
            'formation' => $parcours->getFormation()
        ]);
    }

    #[
        Route('/changer/{ue}/{parcours}', name: 'changer')
    ]
    public function changer(
        Ue       $ue,
        Parcours $parcours
    ): Response {
        return $this->render('structure/ue/_changer.html.twig', [
            'ue' => $ue,
            'parcours' => $parcours,
            'formation' => $parcours->getFormation()
        ]);
    }

    #[
        Route('/changer-ajax/{ue}/{parcours}', name: 'changer_ajax')
    ]
    public function changerAjax(
        Ue       $ue,
        Parcours $parcours
    ): Response {
        //todo: a faire
        return $this->json((new JsonReponse(Response::HTTP_OK, 'message', []))->getReponse());
    }

    #[
        Route('/semestre-ajax', name: 'semestre_ajax')
    ]
    public function semestreAjax(
        Request                    $request,
        SemestreParcoursRepository $semestreParcoursRepository
    ): Response {
        $semestres = $semestreParcoursRepository->findBy([
            'parcours' => $request->query->get('parcours')
        ], ['ordre' => 'ASC']);

        $t = [];
        foreach ($semestres as $semestre) {
            $t[] = [
                'id' => $semestre->getSemestre()?->getId(),
                'libelle' => $semestre->display()
            ];
        }

        return $this->json($t);
    }

    #[Route('/dupliquer-ajax/{ue}/{parcours}', name: 'dupliquer_ajax')]
    public function dupliquerAjax(
        FicheMatiereMutualisableRepository $ficheMatiereMutualisableRepository,
        ParcoursRepository                $parcoursRepository,
        EntityManagerInterface             $entityManager,
        UeRepository                       $ueRepository,
        SemestreRepository                 $semestreRepository,
        Request                            $request,
        Ue                                 $ue,
        Parcours                           $parcours
    ): Response {
        $data = JsonRequest::getFromRequest($request);
        $ordre = (int)$data['position'];
        $semestre = $semestreRepository->find($data['destination']);
        $parcoursDestination = $parcoursRepository->find($data['parcours']);

        if (null !== $semestre && null !== $parcoursDestination) {
            //on récupère, s'il existe, le semestre à la position de destination
            $ueDestination = $ueRepository->findOneBy([
                'semestre' => $semestre,
                'ordre' => $ordre
            ]);

            //on supprime le semestre de la position de destination
            if (null !== $ueDestination) {
                foreach ($ueDestination->getElementConstitutifs() as $ec) {
                    $entityManager->remove($ec);
                }
                $entityManager->remove($ueDestination);
                $entityManager->flush();
            }


            //on clone le semestre et sa structure
            $newUe = clone $ue;
            $newUe->setSemestre($semestre);
            $newUe->setOrdre($ordre);
            $entityManager->persist($newUe);
            // on recopie les UE

            //on recopie les EC
            foreach ($ue->getElementConstitutifs() as $ec) {
                switch ($data['dupliquer']) {
                    case 'mutualise':
                        //vérifier si pas déjà mutualisé
                        $ficheMatiereMutualisable = $ficheMatiereMutualisableRepository->findBy([
                            'ficheMatiere' => $ec->getFicheMatiere(),
                            'parcours' => $parcoursDestination
                        ]);

                        if (count($ficheMatiereMutualisable) === 0) {
                            //si pas mutualisé, mutualiser
                            $ficheMatiereMutualisable = new FicheMatiereMutualisable();
                            $ficheMatiereMutualisable->setFicheMatiere($ec->getFicheMatiere());
                            $ficheMatiereMutualisable->setParcours($parcoursDestination);
                            $entityManager->persist($ficheMatiereMutualisable);
                        }

                        if ($ec->getEcParent() === null) {
                            //faire le lien dans les deux UE
                            $newEc = clone $ec;
                            $newEc->setUe($newUe);
                            $entityManager->persist($newEc);

                            foreach ($ec->getEcEnfants() as $ecEnfant) {
                                $ficheMatiereMutualisable = $ficheMatiereMutualisableRepository->findBy([
                                    'ficheMatiere' => $ecEnfant->getFicheMatiere(),
                                    'parcours' => $parcoursDestination
                                ]);

                                if (count($ficheMatiereMutualisable) === 0) {
                                    $ficheMatiereMutualisable = new FicheMatiereMutualisable();
                                    $ficheMatiereMutualisable->setFicheMatiere($ecEnfant->getFicheMatiere());
                                    $ficheMatiereMutualisable->setParcours($parcoursDestination);
                                    $entityManager->persist($ficheMatiereMutualisable);
                                }

                                //faire le lien dans les deux UE
                                $newEcEnfant = clone $ecEnfant;
                                $newEcEnfant->setUe($newUe);
                                $newEcEnfant->setEcParent($newEc);
                                $newEcEnfant->setFicheMatiere($ecEnfant->getFicheMatiere());
                                $entityManager->persist($newEcEnfant);

                            }
                        }
                        $entityManager->flush();

                        break;
                    case 'recopie':
                        if ($ec->getEcParent() === null) {
                            $newEc = clone $ec;
                            $newEc->setUe($newUe);
                            $entityManager->persist($newEc);
                            if ($ec->getFicheMatiere() !== null) {
                                $newFm = clone $ec->getFicheMatiere();
                                $newFm->setParcours($parcoursDestination);
                                $newEc->setFicheMatiere($newFm);
                                $entityManager->persist($newFm);
                            }

                            foreach ($ec->getEcEnfants() as $ecEnfant) {
                                $newEcEnfant = clone $ecEnfant;
                                $newEcEnfant->setUe($newUe);
                                $newEcEnfant->setEcParent($newEc);
                                $entityManager->persist($newEcEnfant);
                                if ($ecEnfant->getFicheMatiere() !== null) {
                                    $newFm = clone $ecEnfant->getFicheMatiere();
                                    $newFm->setParcours($parcoursDestination);
                                    $newEcEnfant->setFicheMatiere($newFm);
                                    $entityManager->persist($newFm);
                                }
                            }
                        }
                        $entityManager->flush();
                        break;
                }
            }

            // on recopie les UE enfants
            foreach ($ue->getUeEnfants() as $ueEnfant) {
                $newUeEnfant = clone $ueEnfant;
                $newUeEnfant->setSemestre($semestre);
                $newUeEnfant->setUeParent($newUe);
                $entityManager->persist($newUeEnfant);
                //on recopie les EC
                foreach ($ueEnfant->getElementConstitutifs() as $ec) {
                    switch ($data['dupliquer']) {
                        case 'mutualise':
                            //vérifier si pas déjà mutualisé
                            $ficheMatiereMutualisable = $ficheMatiereMutualisableRepository->findBy([
                                'ficheMatiere' => $ec->getFicheMatiere(),
                                'parcours' => $parcoursDestination
                            ]);

                            if (count($ficheMatiereMutualisable) === 0) {
                                //si pas mutualisé, mutualiser
                                //le parcours d'origine
                                $ficheMatiereMutualisable = new FicheMatiereMutualisable();
                                $ficheMatiereMutualisable->setFicheMatiere($ec->getFicheMatiere());
                                $ficheMatiereMutualisable->setParcours($parcoursDestination);
                                $entityManager->persist($ficheMatiereMutualisable);
                            }

                            //faire le lien dans les deux UE
                            $newEc = clone $ec;
                            $newEc->setUe($newUeEnfant);
                            $entityManager->persist($newEc);
                            $entityManager->flush();
                            break;
                        case 'recopie':
                            if ($ec->getEcParent() === null) {
                                $newEc = clone $ec;
                                $newEc->setUe($newUeEnfant);
                                $entityManager->persist($newEc);
                                if ($ec->getFicheMatiere() !== null) {
                                    $newFm = clone $ec->getFicheMatiere();
                                    $newFm->setParcours($parcoursDestination);
                                    $newEc->setFicheMatiere($newFm);
                                    $entityManager->persist($newFm);
                                }
                                foreach ($ec->getEcEnfants() as $ecEnfant) {
                                    $newEcEnfant = clone $ecEnfant;
                                    $newEcEnfant->setUe($newUeEnfant);
                                    $newEcEnfant->setEcParent($newEc);
                                    $entityManager->persist($newEcEnfant);
                                    if ($ec->getFicheMatiere() !== null) {
                                        $newFm = clone $ec->getFicheMatiere();
                                        $newFm->setParcours($parcoursDestination);
                                        $newEcEnfant->setFicheMatiere($newFm);
                                        $entityManager->persist($newFm);
                                    }
                                }
                            }
                            $entityManager->flush();
                            break;
                    }
                }
            }

            $entityManager->flush();

            return $this->json((new JsonReponse(Response::HTTP_OK, 'L\'UE a été dupliquée', []))->getReponse());
        }

        return $this->json((new JsonReponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Erreur lors de la duplication de l\'UE', []))->getReponse());
    }
}
