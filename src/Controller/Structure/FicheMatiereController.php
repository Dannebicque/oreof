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
use Symfony\Component\Routing\Attribute\Route;

#[Route('/structure/fiche-matiere', name: 'structure_fiche_matiere_')]
class FicheMatiereController extends BaseController
{
    #[Route('/', name: 'index')]
    public function index(
        Request $request
    ): Response {
        return $this->render(
            'structure/fiche_matiere/index.html.twig',
            [
                'type' => $request->query->get('type', 'parcours'),
                'page' => $request->query->get('page', 1),
            ]
        );
    }

    #[Route('/liste', name: 'liste')]
    public function liste(
        Request                $request,
        FicheMatiereRepository $ficheMatiereRepository
    ): Response {

        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_SES')) {
            $ficheMatieres = $ficheMatiereRepository->findByAdmin(
                $this->getCampagneCollecte(),
                $request->query->all()
            );
            $totalFiches = $ficheMatiereRepository->countByAdmin($this->getCampagneCollecte(), $request->query->all());
        } else {
            $ficheMatieres = $ficheMatiereRepository->findByResponsable(
                $this->getUser(),
                $this->getCampagneCollecte(),
                $request->query->all()
            );

            $totalFiches = $ficheMatiereRepository->countByResponsable(
                $this->getUser(),
                $this->getCampagneCollecte(),
                $request->query->all()
            );
        }

        $tFicheMatieres = [];


        $tMentions = [];
        $tParcours = [];
        $tUsers = [];
        foreach ($ficheMatieres as $fiche) {
            $tFicheMatieres[$fiche->getId()] = $fiche;

            if (null !== $fiche->getParcours()) {
                $tParcours[$fiche->getParcours()->getId()] = $fiche->getParcours();
                if ($fiche->getParcours()->getFormation() !== null) {
                    $tMentions[$fiche->getParcours()->getFormation()->getId()] = $fiche->getParcours()->getFormation();
                }
            }

            if (null !== $fiche->getResponsableFicheMatiere()) {
                $tUsers[$fiche->getResponsableFicheMatiere()->getId()] = $fiche->getResponsableFicheMatiere();
            }
        }


        $nbPages = ceil($totalFiches / 50);

        return $this->render('structure/fiche_matiere/_liste.html.twig', [
            'ficheMatieres' => $tFicheMatieres,
            'deplacer' => false,
            'mode' => 'liste',
            'mentions' => $tMentions,
            'parcours' => $tParcours,
            'users' => $tUsers,
            'params' => $request->query->all(),
            'totalFiches' => $totalFiches,
            'nbPages' => $nbPages,
        ]);
    }

    #[Route('/liste/hors-diplome', name: 'liste_hd')]
    public function listeHorsDiplome(
        Request                $request,
        FicheMatiereRepository $ficheMatiereRepository
    ): Response {
        $ficheMatieres = $ficheMatiereRepository->findByHd(
            $this->getCampagneCollecte(),
            $request->query->all(),
        );

        $totalFiches = $ficheMatiereRepository->countByHd($this->getCampagneCollecte(), $request->query->all());
        $nbPages = ceil($totalFiches / 50);

        return $this->render('structure/fiche_matiere/_listeHd.html.twig', [
            'ficheMatieres' => $ficheMatieres,
            'deplacer' => false,
            'mode' => 'liste',
            'params' => $request->query->all(),
            'totalFiches' => $totalFiches,
            'nbPages' => $nbPages,

        ]);
    }

    #[Route('/detail/ue/{ue}/{parcours}', name: 'detail_ue')]
    public function detailComposante(
        ElementConstitutifRepository $elementConstitutifRepository,
        Ue                           $ue,
    ): Response {
        $ecs = $elementConstitutifRepository->findByUe($ue);

        return $this->render('structure/fiche_matiere/_liste.html.twig', [
            'ecs' => $ecs,
            'mode' => 'detail',
            'sort' => 'libelle',
            'direction' => 'asc'
        ]);
    }
}
