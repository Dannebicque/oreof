<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Structure/SemestreController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Structure;

use App\Classes\JsonReponse;
use App\Classes\SemestreOrdre;
use App\Entity\FicheMatiereMutualisable;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreMutualisable;
use App\Entity\SemestreParcours;
use App\Entity\Ue;
use App\Repository\ComposanteRepository;
use App\Repository\FicheMatiereMutualisableRepository;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\Repository\SemestreMutualisableRepository;
use App\Repository\SemestreParcoursRepository;
use App\TypeDiplome\TypeDiplomeRegistry;
use App\Utils\JsonRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/semestre', name: 'structure_semestre_')
]
class SemestreController extends AbstractController
{
    #[
        Route('/detail/parcours/{parcours}', name: 'detail_parcours')
    ]
    public function detailParcours(
        TypeDiplomeRegistry        $registry,
        SemestreParcoursRepository $semestreRepository,
        Parcours                   $parcours
    ): Response {
        $formation = $parcours->getFormation();
        if (null === $formation) {
            throw $this->createNotFoundException('La formation n\'existe pas');
        }
        $typeDiplome = $formation->getTypeDiplome();

        if (null === $typeDiplome) {
            throw $this->createNotFoundException('Le type de diplôme n\'existe pas');
        }

        $sems = $semestreRepository->findByParcours($parcours);

        $semestres = [];
        foreach ($sems as $sem) {
            $semestres[$sem->getOrdre()] = $sem;
        }

        $debut = $parcours->getFormation()?->getSemestreDebut();

        return $this->render('structure/semestre/_liste.html.twig', [
            'semestres' => $semestres,
            'parcours' => $parcours,
            'debut' => $debut,
            'fin' => $typeDiplome->getSemestreFin(),
        ]);
    }

    #[
        Route('/dupliquer/{semestre}/{parcours}', name: 'dupliquer')
    ]
    public function dupliquer(
        Semestre $semestre,
        Parcours $parcours
    ): Response {
        return $this->render('structure/semestre/_dupliquer.html.twig', [
            'semestre' => $semestre,
            'parcours' => $parcours,
            'formation' => $parcours->getFormation()
        ]);
    }

    #[
        Route('/actions/{parcours}', name: 'actions')
    ]
    public function init(
        EntityManagerInterface     $entityManager,
        SemestreParcoursRepository $semestreParcoursRepository,
        Request                    $request,
        Parcours                   $parcours
    ): Response {
        $ordre = JsonRequest::getValueFromRequest($request, 'position');
        $action = JsonRequest::getValueFromRequest($request, 'action');
        $semestres = $semestreParcoursRepository->findBy([
            'parcours' => $parcours,
            'ordre' => $ordre
        ]);

        if (count($semestres) > 0) {
            return JsonReponse::error('Le semestre existe déjà');
        }

        switch ($action) {
            case 'init':
                $semestre = new Semestre();
                $semestre->setOrdre($ordre);
                $entityManager->persist($semestre);

                $semestreParcours = new SemestreParcours($semestre, $parcours);
                $semestreParcours->setOrdre($ordre);
                $entityManager->persist($semestreParcours);

                $typeDiplome = $parcours->getFormation()?->getTypeDiplome();
                if (null !== $typeDiplome && $typeDiplome->getNbUeMin() > 0) {
                    for ($i = 1; $i <= $typeDiplome->getNbUeMin(); $i++) {
                        $ue = new Ue();
                        $ue->setSemestre($semestre);
                        $ue->setOrdre($i);
                        $entityManager->persist($ue);
                    }
                }

                $entityManager->flush();
                return JsonReponse::success('Semestre ajouté');
            case 'nonDispense':
                $semestre = new Semestre();
                $semestre->setOrdre($ordre);
                $semestre->setNonDispense(true);
                $entityManager->persist($semestre);

                $semestreParcours = new SemestreParcours($semestre, $parcours);
                $semestreParcours->setOrdre($ordre);
                $entityManager->persist($semestreParcours);
                $entityManager->flush();
                return JsonReponse::success('Semestre marqué comme non dispensé');
        }
    }

    #[Route('/changer/{semestre}/{parcours}', name: 'changer')]
    public function changer(
        Semestre $semestre,
        Parcours $parcours
    ): Response {
        return $this->render('structure/semestre/_changer.html.twig', [
            'semestre' => $semestre,
            'parcours' => $parcours,
            'formation' => $parcours->getFormation()
        ]);
    }

    #[Route('/changer-ajax/{semestre}/{parcours}', name: 'changer_ajax')]
    public function changerAjax(
        EntityManagerInterface     $entityManager,
        SemestreParcoursRepository $semestreParcoursRepository,
        ParcoursRepository         $parcoursRepository,
        Request                    $request,
        Semestre                   $semestre,
        Parcours                   $parcours
    ): Response {
        $data = JsonRequest::getFromRequest($request);
        //todo: on ne peut pas déplacer un semestre tronc commun ? Sauf vers une autre formation?

        //Parcours de destination
        $parcoursDestination = $parcoursRepository->find($data['destination']);
        $ordre = (int)$data['position'];
        if (null !== $parcoursDestination) {
            //on récupère, s'il existe, le semestre à la position de destination
            $semestreDest = $semestreParcoursRepository->findOneBy([
                'parcours' => $parcoursDestination,
                'ordre' => $ordre
            ]);

            //on supprime le semestre de la position de destination
            if (null !== $semestreDest) {
                $sem = $semestreDest->getSemestre();
                if ($sem !== null) {
                    if ($sem->getSemestreParcours()->count() === 1) {
                        //donc uniquement mon parcours, on peut supprimer le semestre et sa structure
                        $entityManager->remove($sem);
                        $entityManager->flush();
                    }
                }
                $semestreParcoursRepository->remove($semestreDest, true);
            }


            foreach ($semestre->getSemestreParcours() as $semestreParcour) {
                if ($semestreParcour->getParcours() === $parcours) {
                    $semestreParcour->setParcours($parcoursDestination);
                    $semestreParcour->setOrdre((int)$ordre);
                    $semestreParcoursRepository->save($semestreParcour, true);
                }
            }

            return $this->json((new JsonReponse(Response::HTTP_OK, 'message', []))->getReponse());
        }

        return $this->json((new JsonReponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'message', []))->getReponse());
    }

    #[Route('/dupliquer-ajax/{semestre}/{parcours}', name: 'dupliquer_ajax')]
    public function dupliquerAjax(
        Request                            $request,
        EntityManagerInterface             $entityManager,
        FicheMatiereMutualisableRepository $ficheMatiereMutualisableRepository,
        SemestreParcoursRepository         $semestreParcoursRepository,
        ParcoursRepository                 $parcoursRepository,
        Semestre                           $semestre,
        Parcours                           $parcours
    ): Response {
        $data = JsonRequest::getFromRequest($request);

        $parcoursDestination = $parcoursRepository->find($data['destination']);
        $ordre = (int)$data['position'];
        if (null !== $parcoursDestination) {
            //on récupère, s'il existe, le semestre à la position de destination
            $semestreDest = $semestreParcoursRepository->findOneBy([
                'parcours' => $parcoursDestination,
                'ordre' => $ordre
            ]);

            //on supprime le semestre de la position de destination
            if (null !== $semestreDest) {
                $sem = $semestreDest->getSemestre();
                if ($sem !== null) {
                    if ($sem->getSemestreParcours()->count() === 1) {
                        foreach ($sem->getUes() as $ue) {
                            foreach ($ue->getElementConstitutifs() as $ec) {
                                $entityManager->remove($ec);
                            }
                            $entityManager->remove($ue);
                        }
                        //donc uniquement mon parcours, on peut supprimer le semestre et sa structure
                        $entityManager->remove($sem);
                        $entityManager->flush();
                    }
                }
                $semestreParcoursRepository->remove($semestreDest, true);
            }


            //on clone le semestre et sa structure
            $newSemestre = clone $semestre;
            $entityManager->persist($newSemestre);
            // on recopie les UE
            foreach ($semestre->getUes() as $ue) {
                if ($ue->getUeParent() === null) {
                    $newUe = clone $ue;
                    $newUe->setSemestre($newSemestre);
                    $newUe->setUeParent(null);
                    $entityManager->persist($newUe);
                    //on recopie les EC
                    foreach ($ue->getElementConstitutifs() as $ec) {
                        switch ($data['dupliquer']) {
                            case 'mutualise':
                                //vérifier si pas déjà mutualisé
                                $fm = $ficheMatiereMutualisableRepository->findBy([
                                    'ficheMatiere' => $ec->getFicheMatiere(),
                                ]);

                                if (count($fm) === 0) {
                                    //si pas mutualisé, mutualiser
                                    //le parcours d'origine
                                    $ficheMatiereMutualisable = new FicheMatiereMutualisable();
                                    $ficheMatiereMutualisable->setFicheMatiere($ec->getFicheMatiere());
                                    $ficheMatiereMutualisable->setParcours($parcours);
                                    $entityManager->persist($ficheMatiereMutualisable);
                                }

                                //le futur parcours de destination
                                $ficheMatiereMutualisable = new FicheMatiereMutualisable();
                                $ficheMatiereMutualisable->setFicheMatiere($ec->getFicheMatiere());
                                $ficheMatiereMutualisable->setParcours($parcoursDestination);
                                $entityManager->persist($ficheMatiereMutualisable);
                                $entityManager->flush();

                                //faire le lien dans les deux UE
                                $newEc = clone $ec;
                                $newEc->setUe($newUe);
                                $entityManager->persist($newEc);
                                $entityManager->flush();
                                break;
                            case 'recopie':
                                $newEc = clone $ec;
                                $newEc->setUe($newUe);
                                $entityManager->persist($newEc);
                                if ($ec->getFicheMatiere() !== null) {
                                    $newFm = clone $ec->getFicheMatiere();
                                    $newEc->setFicheMatiere($newFm);
                                    $entityManager->persist($newFm);
                                }
                                $entityManager->flush();
                                break;
                        }
                    }

                    // on recopie les UE enfants
                    foreach ($ue->getUeEnfants() as $ueEnfant) {
                        $newUeEnfant = clone $ueEnfant;
                        $newUeEnfant->setSemestre($newSemestre);
                        $newUeEnfant->setUeParent($newUe);
                        $entityManager->persist($newUeEnfant);
                        //on recopie les EC
                        foreach ($ueEnfant->getElementConstitutifs() as $ec) {
                            switch ($data['dupliquer']) {
                                case 'mutualise':
                                    //vérifier si pas déjà mutualisé
                                    $fm = $ficheMatiereMutualisableRepository->findBy([
                                        'ficheMatiere' => $ec->getFicheMatiere(),
                                    ]);

                                    if (count($fm) === 0) {
                                        //si pas mutualisé, mutualiser
                                        //le parcours d'origine
                                        $ficheMatiereMutualisable = new FicheMatiereMutualisable();
                                        $ficheMatiereMutualisable->setFicheMatiere($ec->getFicheMatiere());
                                        $ficheMatiereMutualisable->setParcours($parcours);
                                        $entityManager->persist($ficheMatiereMutualisable);
                                    }

                                    //le futur parcours de destination
                                    $ficheMatiereMutualisable = new FicheMatiereMutualisable();
                                    $ficheMatiereMutualisable->setFicheMatiere($ec->getFicheMatiere());
                                    $ficheMatiereMutualisable->setParcours($parcoursDestination);
                                    $entityManager->persist($ficheMatiereMutualisable);
                                    $entityManager->flush();

                                    //faire le lien dans les deux UE
                                    $newEc = clone $ec;
                                    $newEc->setUe($newUeEnfant);
                                    $entityManager->persist($newEc);
                                    $entityManager->flush();
                                    break;
                                case 'recopie':
                                    $newEc = clone $ec;
                                    $newEc->setUe($newUeEnfant);
                                    $entityManager->persist($newEc);
                                    if ($ec->getFicheMatiere() !== null) {
                                        $newFm = clone $ec->getFicheMatiere();
                                        $newEc->setFicheMatiere($newFm);
                                        $entityManager->persist($newFm);
                                    }
                                    $entityManager->flush();
                                    break;
                            }
                        }
                    }
                }
            }


            //on ajoute au parcours
            $semestreParcours = new SemestreParcours($newSemestre, $parcoursDestination);
            $semestreParcours->setOrdre($ordre);
            $entityManager->persist($semestreParcours);
            $entityManager->flush();

            return $this->json((new JsonReponse(Response::HTTP_OK, 'message', []))->getReponse());
        }

        return $this->json((new JsonReponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'message', []))->getReponse());
    }

    #[Route('/mutualiser/{semestre}/{parcours}', name: 'mutualiser')]
    public function mutualiser(
        ComposanteRepository $composanteRepository,
        Semestre             $semestre,
        Parcours             $parcours
    ): Response {
        return $this->render('structure/semestre/_mutualiser.html.twig', [
            'semestre' => $semestre,
            'parcours' => $parcours,
            'composantes' => $composanteRepository->findAll()
        ]);
    }

    #[Route('/{semestre}/mutualise/ajax', name: 'mutualise_add_ajax', methods: [
        'POST',
        'DELETE'
    ])]
    public function mutualiseAjax(
        EntityManagerInterface         $entityManager,
        Request                        $request,
        FormationRepository            $formationRepository,
        ParcoursRepository             $parcoursRepository,
        SemestreMutualisableRepository $semestreMutualisableRepository,
        Semestre                       $semestre,
    ): Response {
        $data = JsonRequest::getFromRequest($request);
        $t = [];
        switch ($data['field']) {
            case 'decrocher':
                $semestre->setSemestreRaccroche(null);
                $entityManager->flush();
                return $this->json(true);
            case 'raccrocher':
                $uem = $semestreMutualisableRepository->find($data['value']);
                if ($uem !== null) {
                    $semestre->setSemestreRaccroche($uem);
                    $entityManager->flush();
                    return $this->json(true);
                }

                return $this->json(
                    ['error' => 'Semestre non trouvé'],
                    500
                );
            case 'liste':
                return $this->render('structure/semestre/_liste_mutualise.html.twig', [
                    'semestres' => $semestreMutualisableRepository->findBy(['semestre' => $semestre]),
                    'semestre' => $semestre
                ]);
            case 'formation':
                $formations = $formationRepository->findBy(['composantePorteuse' => $data['value']], ['libelle' => 'ASC']);
                foreach ($formations as $formation) {
                    $t[] = [
                        'id' => $formation->getId(),
                        'libelle' => $formation->getTypeDiplome()?->getLibelle() . ' - ' . $formation->getDisplay()
                    ];
                }
                break;
            case 'parcours':
                $parcours = $parcoursRepository->findBy(['formation' => $data['value']], ['libelle' => 'ASC']);
                foreach ($parcours as $parcour) {
                    $t[] = [
                        'id' => $parcour->getId(),
                        'libelle' => $parcour->getLibelle()
                    ];
                }
                break;
            case 'save':
                if (!isset($data['parcours']) || $data['parcours'] === '') {
                    $formation = $formationRepository->find($data['formation']);
                    if ($formation !== null && $formation->isHasParcours() === false && count($formation->getParcours()) === 1) {
                        $parcours = $formation->getParcours()[0];
                    }
                } else {
                    $parcours = $parcoursRepository->find($data['parcours']);
                }


                $exist = $semestreMutualisableRepository->findOneBy([
                    'semestre' => $semestre,
                    'parcours' => $parcours
                ]);

                if ($exist === null) {
                    $semestreMutualise = new SemestreMutualisable();
                    $semestreMutualise->setSemestre($semestre);
                    $semestreMutualise->setParcours($parcours);
                    $entityManager->persist($semestreMutualise);
                    $entityManager->flush();
                }

                return $this->json(true);
            case 'delete':
                $sem = $semestreMutualisableRepository->find($data['sem']);
                //todo: vérifier si pas utilisé

                if ($sem !== null) {
                    $entityManager->remove($sem);
                    $entityManager->flush();
                }

                return $this->json(true);
        }

        return $this->json($t);
    }

    #[Route('/raccrocher/{semestre}/{parcours}', name: 'raccrocher')]
    public function raccrocher(
        SemestreMutualisableRepository $semestreMutualisableRepository,
        Semestre                       $semestre,
        Parcours                       $parcours
    ): Response {
        $semestres = $semestreMutualisableRepository->findBy(['parcours' => $parcours]);

        return $this->render('structure/semestre/_raccrocher.html.twig', [
            'semestre' => $semestre,
            'semestres' => $semestres,
            'parcours' => $parcours
        ]);
    }

    #[Route('/deplacer/{semestre}/{parcours}/{sens}', name: 'deplacer', methods: ['GET'])]
    public function deplacer(
        SemestreOrdre    $semestreOrdre,
        SemestreParcours $semestre,
        Parcours         $parcours,
        string           $sens
    ): Response {
        $semestreOrdre->deplacerSemestre($semestre, $parcours, $sens);

        return $this->json(true);
    }

//    #[Route('/data/{semestre}/{parcours}', name: 'data')]
//    public function data(
//        Request                    $request,
//        SemestreParcoursRepository $semestreRepository,
//        Semestre                   $semestre,
//        Parcours                   $parcours
//    ): Response {
//        $data = JsonRequest::getFromRequest($request);
//
//        switch ($data['action']) {
//            case 'mutualise':
//                return $this->render('structure/semestre/_mutualise.html.twig', [
//                    'semestre' => $semestre,
//                    'parcours' => $parcours
//                ]);
//            case 'reutilise':
//                return $this->render('structure/semestre/_reutilise.html.twig', [
//                    'semestre' => $semestre,
//                    'parcours' => $parcours
//                ]);
//        }
//    }
}
