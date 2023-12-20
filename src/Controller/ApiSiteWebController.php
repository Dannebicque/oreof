<?php

namespace App\Controller;

use App\Classes\GetHistorique;
use App\Repository\FormationRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ApiSiteWebController extends AbstractController
{
    #[Route('/api/site/web', name: 'app_api_site_web')]
    public function index(
        GetHistorique $getHistorique,
        FormationRepository $formatinRepository,
    ): JsonResponse
    {
        $data = [];
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
                'parcours' => $tParcours,
                //todo: on pourrait ajouter la version. Le Lheo doit dépendre de la version
                'dateValidation' => $getHistorique->getHistoriqueFormationLastStep($formation, 'publication')?->getDate()?->format('Y-m-d H:i:s') ?? null,
            ];
        }

        return new JsonResponse($data);
    }
}
