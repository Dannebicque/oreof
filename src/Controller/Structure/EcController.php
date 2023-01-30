<?php

namespace App\Controller\Structure;
use App\Entity\Ue;
use App\Repository\ElementConstitutifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/element-constitutif', name: 'structure_ec_')
]
class EcController extends AbstractController
{
    #[
        Route('/', name: 'index')
    ]
    public function index()
    {
        return $this->render('structure/ec/index.html.twig', [

        ]);
    }

    #[
        Route('/liste', name: 'liste')
    ]
    public function liste(ElementConstitutifRepository $elementConstitutifRepository,)
    {
        $ecs = $elementConstitutifRepository->findByRoleUser($this->getUser());
        return $this->render('structure/ec/_liste.html.twig', [
            'ecs' => $ecs
        ]);
    }

    #[
        Route('/detail/ue/{ue}', name: 'detail_ue')
    ]
    public function detailComposante(
        ElementConstitutifRepository $elementConstitutifRepository,
        Ue $ue)
    {
        $ecs = $elementConstitutifRepository->findBy(['ue' => $ue]);

        return $this->render('structure/ec/_liste.html.twig', [
            'ecs' => $ecs,
            'ue' => $ue
        ]);
    }
}
