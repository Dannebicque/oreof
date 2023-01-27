<?php

namespace App\Controller\API;

use App\Repository\ComposanteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComposanteController extends AbstractController
{
    #[Route('/api/composante', name: 'api_composante')]

    public function getComposante(
        ComposanteRepository $composanteRepository,
    ): Response
    {
        $composantes = $composanteRepository->findAll();
        $t = [];
        foreach ($composantes as $composante) {
            $t[] = [
                'id' => $composante->getId(),
                'libelle' => $composante->getLibelle(),
            ];
        }
        return $this->json($t);
    }
}
