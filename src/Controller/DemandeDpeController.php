<?php

namespace App\Controller;

use App\Entity\Composante;
use App\Repository\ComposanteRepository;
use App\Repository\DpeDemandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DemandeDpeController extends AbstractController
{
    #[Route('/demande/dpe', name: 'app_demande_dpe')]
    #[IsGranted('ROLE_SES')]
    public function index(
        DpeDemandeRepository $dpeDemandeRepository,
    ): Response
    {
        return $this->render('demande_dpe/index.html.twig', [
            'demandes' => $dpeDemandeRepository->findAll(),
        ]);
    }

    #[Route('/demande/dpe/composante/{composante}', name: 'app_demande_dpe_composante')]
    public function dpeComposante(
        Composante $composante,
        DpeDemandeRepository $dpeDemandeRepository,
    ): Response
    {
        $this->denyAccessUnlessGranted('CAN_COMPOSANTE_SHOW_MY', $composante);

        return $this->render('demande_dpe/index.html.twig', [
            'demandes' => $dpeDemandeRepository->findByComposante($composante),
        ]);
    }

    //si acceptation ajouter à l'historique, mail DPE +RF? RP? + changement état workflow. Gérer workflow avec ou sans SES
}
