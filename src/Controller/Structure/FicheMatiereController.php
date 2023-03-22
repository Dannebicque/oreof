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
use App\Entity\Parcours;
use App\Entity\Ue;
use App\Repository\EcUeRepository;
use App\Repository\ElementConstitutifRepository;
use App\Repository\FicheMatiereRepository;
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
    public function liste(FicheMatiereRepository $ficheMatiereRepository): Response
    {
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
        $ficheMatieres = $ficheMatiereRepository->findAll();
        return $this->render('structure/fiche_matiere/_liste.html.twig', [
            'ficheMatieres' => $ficheMatieres,
            'deplacer' => false,
            'mode' => 'liste',
        ]);
    }

    #[
        Route('/detail/ue/{ue}/{parcours}', name: 'detail_ue')
    ]
    public function detailComposante(
        EcUeRepository $ecUeRepository,
        Ue $ue,
//        Parcours $parcours
    ): Response {
        $ecs = $ecUeRepository->findByUe($ue);

        return $this->render('structure/fiche_matiere/_liste.html.twig', [
            'ecs' => $ecs,
//            'ue' => $ue,
//            'parcours' => $parcours,
//            'deplacer' => true,
            'mode' => 'detail'
        ]);
    }
}
