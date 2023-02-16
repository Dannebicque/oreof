<?php

namespace App\Controller\Structure;

use App\Repository\ComposanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[
    Route('/structure/composante', name: 'structure_composante_')
]
class ComposanteController extends AbstractController
{
    #[
        Route('/', name: 'index')
    ]
    public function index(): Response
    {
        return $this->render('structure/composante/index.html.twig', [
        ]);
    }

    #[
        Route('/liste', name: 'liste')
    ]
    public function liste(
        ComposanteRepository $composanteRepository
    ): Response
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_COMPOSANTE_SHOW_ALL', $this->getUser())) {
            $composantes = $composanteRepository->findAll();
        } else {
            $composantes = $composanteRepository->findByCentreGestion($this->getUser());
        }

        return $this->render('structure/composante/_liste.html.twig', [
            'composantes' => $composantes
        ]);
    }
}
