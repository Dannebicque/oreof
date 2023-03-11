<?php

namespace App\Controller\Structure;

use App\Controller\BaseController;
use App\Entity\Parcours;
use App\Entity\Ue;
use App\Repository\EcUeRepository;
use App\Repository\ElementConstitutifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/element-constitutif', name: 'structure_ec_')
]
class EcController extends BaseController
{
    #[
        Route('/', name: 'index')
    ]
    public function index(): Response
    {
        return $this->render('structure/ec/index.html.twig');
    }

    #[Route('/liste', name: 'liste')]
    public function liste(ElementConstitutifRepository $elementConstitutifRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_COMPOSANTE_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('ROLE_FORMATION_SHOW_ALL', $this->getUser()) ||
            $this->isGranted('ROLE_EC_SHOW_ALL', $this->getUser())) {
            $ecs = $elementConstitutifRepository->findByAllAnneUniversitaire($this->getAnneeUniversitaire());
        } else {
            $ecs = [];
            $ecs[] = $elementConstitutifRepository->findByComposanteDpe($this->getUser(),
                $this->getAnneeUniversitaire());
            $ecs[] = $elementConstitutifRepository->findByResponsableFormation($this->getUser(),
                $this->getAnneeUniversitaire());
            $ecs[] = $elementConstitutifRepository->findByResponsableEc($this->getUser(),
                $this->getAnneeUniversitaire());
            $ecs = array_merge(...$ecs);
        }
        return $this->render('structure/ec/_liste.html.twig', [
            'ecs' => $ecs,
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
        Parcours $parcours
    ): Response {
        $ecs = $ecUeRepository->findByUe($ue);

        return $this->render('structure/ec/_liste.html.twig', [
            'ecs' => $ecs,
            'ue' => $ue,
            'parcours' => $parcours,
            'deplacer' => true,
            'mode' => 'detail'
        ]);
    }
}
