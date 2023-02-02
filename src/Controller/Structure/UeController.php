<?php

namespace App\Controller\Structure;

use App\Entity\Semestre;
use App\Repository\UeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/ue', name: 'structure_ue_')
]
class UeController extends AbstractController
{
    #[
        Route('/detail/semestre/{semestre}', name: 'detail_semestre')
    ]
    public function detailComposante(
        UeRepository $ueRepository,
        Semestre $semestre): Response
    {
        $ues = $ueRepository->findBy(['semestre' => $semestre]);

        return $this->render('structure/ue/_liste.html.twig', [
            'ues' => $ues
        ]);
    }

}
