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
        if ($this->isGranted('ROLE_SES')) {
            $composantes = $composanteRepository->findAll();
        } elseif ($this->isGranted('ROLE_RESP_DPE')) {
            $composantes = $composanteRepository->findBy(['responsableDpe' => $this->getUser()]);
        } else {
            //todo: find des composantes d'attachement en lecture ?
            $composantes = [];
        }


        return $this->render('structure/composante/_liste.html.twig', [
            'composantes' => $composantes
        ]);
    }
}
