<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Structure/SemestreController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Structure;

use App\Classes\SemestreOrdre;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\SemestreMutualisable;
use App\Repository\ComposanteRepository;
use App\Repository\FormationRepository;
use App\Repository\ParcoursRepository;
use App\Repository\SemestreMutualisableRepository;
use App\Repository\SemestreParcoursRepository;
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
        SemestreParcoursRepository $semestreRepository,
        Parcours                   $parcours
    ): Response {
        $semestres = $semestreRepository->findBy(['parcours' => $parcours]);//todo: filtrer selon droits // ajouter le tronc commun

        return $this->render('structure/semestre/_liste.html.twig', [
            'semestres' => $semestres,
            'parcours' => $parcours
        ]);
    }

    #[
        Route('/mutualiser/{semestre}/{parcours}', name: 'mutualiser')
    ]
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

    #[
        Route('/raccrocher/{semestre}/{parcours}', name: 'raccrocher')
    ]
    public function raccrocher(
        SemestreMutualisableRepository $semestreMutualisableRepository,
        Semestre                   $semestre,
        Parcours                   $parcours
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
        SemestreOrdre $semestreOrdre,
        Semestre      $semestre,
        Parcours      $parcours,
        string        $sens
    ): Response {
        $semestreOrdre->deplacerSemestre($semestre, $parcours, $sens);

        return $this->json(true);
    }

    #[
        Route('/data/{semestre}/{parcours}', name: 'data')
    ]
    public function data(
        Request                    $request,
        SemestreParcoursRepository $semestreRepository,
        Semestre                   $semestre,
        Parcours                   $parcours
    ): Response {
        $data = JsonRequest::getFromRequest($request);

        switch ($data['action']) {
            case 'mutualise':
                return $this->render('structure/semestre/_mutualise.html.twig', [
                    'semestre' => $semestre,
                    'parcours' => $parcours
                ]);
                break;
            case 'reutilise':
                return $this->render('structure/semestre/_reutilise.html.twig', [
                    'semestre' => $semestre,
                    'parcours' => $parcours
                ]);
                break;
        }
    }
}
