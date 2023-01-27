<?php

namespace App\Controller\API;

use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormationController extends AbstractController
{
    #[Route('/api/formation', name: 'api_formation')]
    public function getFormation(
        FormationRepository $formationRepository,
    ): Response
    {
        $formations = $formationRepository->findAll();
        $t = [];
        foreach ($formations as $formation) {
            $t[] = [
                'id' => $formation->getId(),
                'libelle' => $formation->display(),
            ];
        }
        return $this->json($t);
    }
}
