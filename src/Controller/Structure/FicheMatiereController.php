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
        FicheMatiereRepository $ficheMatiereRepository): Response
    {

        $sort = $request->query->get('sort') ?? 'libelle';
        $direction = $request->query->get('direction') ?? 'asc';
        $q = $request->query->get('q') ?? null;

//        if ($this->isGranted('ROLE_ADMIN') ||
//            $this->isGranted('ROLE_COMPOSANTE_SHOW_ALL', $this->getUser()) ||
//            $this->isGranted('ROLE_FORMATION_SHOW_ALL', $this->getUser())) {
//            $ficheMatieres = $ficheMatiereRepository->findByAllAnneUniversitaire($this->getAnneeUniversitaire());
//        } else {
//            $ficheMatieres = [];
//            $ficheMatieres[] = $ficheMatiereRepository->findByComposanteDpe(
//                $this->getUser(),
//                $this->getAnneeUniversitaire()
//            );
//            $ficheMatieres[] = $ficheMatiereRepository->findByResponsableFormation(
//                $this->getUser(),
//                $this->getAnneeUniversitaire()
//            );
//            $ficheMatieres[] = $ficheMatiereRepository->findByResponsableEc(
//                $this->getUser(),
//                $this->getAnneeUniversitaire()
//            );
//            $ficheMatieres = array_merge(...$ficheMatieres);
//        }
        $ficheMatieres = $ficheMatiereRepository->findByAdmin(
            $this->getAnneeUniversitaire(),
            [$sort => $direction],
            $q
        );
        return $this->render('structure/fiche_matiere/_liste.html.twig', [
            'ficheMatieres' => $ficheMatieres,
            'deplacer' => false,
            'mode' => 'liste',
            'sort' => $sort,
            'direction' => $direction
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
