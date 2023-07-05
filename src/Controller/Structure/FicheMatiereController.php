<?php
/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/src/Controller/Structure/EcController.php
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/03/2023 22:08
 */

namespace App\Controller\Structure;

use App\Controller\BaseController;
use App\Entity\Ue;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/fiche-matiere', name: 'structure_fiche_matiere_')
]
class FicheMatiereController extends BaseController
{
    #[
        Route('/', name: 'index')
    ]
    public function index(): Response
    {
        return $this->render('structure/fiche_matiere/index.html.twig');
    }

    #[Route('/liste', name: 'liste')]
    public function liste(
        Request $request,
        FicheMatiereRepository $ficheMatiereRepository
    ): Response {
        $sort = $request->query->get('sort') ?? 'libelle';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SES')) {
            $ficheMatieres = $ficheMatiereRepository->findByAdmin(
                $this->getAnneeUniversitaire(),
                [$sort => $direction],
                $q
            );
        } else {
            $ficheMatieres = [];

            // toutes les fiches en tant que responsable composante/DPE
//            $ficheMatieres[] = $ficheMatiereRepository->findByComposanteDpe(
//                $this->getUser(),
//                $this->getAnneeUniversitaire(),
//                [$sort => $direction],
//                $q
//            );
            // toutes les fiches en tant que responsable formation
            $ficheMatieres[] = $ficheMatiereRepository->findByResponsableFormation(
                $this->getUser(),
                $this->getAnneeUniversitaire(),
                [$sort => $direction],
                $q
            );

            // toutes les fiches en tant que responsable parcours
            $ficheMatieres[] = $ficheMatiereRepository->findByResponsableParcours(
                $this->getUser(),
                $this->getAnneeUniversitaire(),
                [$sort => $direction],
                $q
            );

            // toutes les fiches en tant que responsable fiche matière
            $ficheMatieres[] = $ficheMatiereRepository->findByResponsableFicheMatiere(
                $this->getUser(),
                $this->getAnneeUniversitaire(),
                [$sort => $direction],
                $q
            );
            $tFicheMatieres = [];
            foreach ($ficheMatieres as $ficheMatiere) {
                foreach ($ficheMatiere as $fiche) {
                    $tFicheMatieres[$fiche->getId()] = $fiche;
                }
            }
        }

        return $this->render('structure/fiche_matiere/_liste.html.twig', [
            'ficheMatieres' => $tFicheMatieres,
            'deplacer' => false,
            'mode' => 'liste',
            'mentions' => [],
            'parcours' => [],
            'params' => $request->query->all()
        ]);
    }

    #[
        Route('/detail/ue/{ue}/{parcours}', name: 'detail_ue')
    ]
    public function detailComposante(
        ElementConstitutifRepository $elementConstitutifRepository,
        Ue $ue,
    ): Response {
        $ecs = $elementConstitutifRepository->findByUe($ue);

        return $this->render('structure/fiche_matiere/_liste.html.twig', [
            'ecs' => $ecs,
//            'ue' => $ue,
//            'parcours' => $parcours,
//            'deplacer' => true,
            'mode' => 'detail',
            'sort' => 'libelle',
            'direction' => 'asc'
        ]);
    }
}
