<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ApiSiteWebController extends AbstractController
{
    #[Route('/api/site/web', name: 'app_api_site_web')]
    public function index(
        FormationRepository $formatinRepository,
    ): JsonResponse
    {
        $formations = $formatinRepository->findAll();
        foreach ($formations as $formation) {
            $tParcours = [];
            foreach ($formation->getParcours() as $parcours) {
                $tParcours[] = [
                    'id' => $parcours->getId(),
                    'libelle' => $parcours->getLibelle(),
                    'url' => $this->generateUrl('app_parcours_export_json_urca', ['parcours' => $parcours->getId()], UrlGenerator::ABSOLUTE_URL)
                ];
            }


            $data[] = [
                'id' => $formation->getId(),
                'libelle' => $formation->getDisplayLong(),
                'parcours' => $tParcours
            ];
        }

        return new JsonResponse($data);
    }
}
