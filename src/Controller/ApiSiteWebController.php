<?php

namespace App\Controller;

use App\Classes\GetHistorique;
use App\Entity\DpeParcours;
use App\Repository\FormationRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
            /**
             * 
             * NE PRENDRE QUE LES PARCOURS QUI SONT ÉTIQUETÉS COMME PUBLIÉS
             *  - Filtrer avec : array_filter()
             * 
             */
            $tParcours = [];
            foreach ($formation->getParcours() as $parcours) {
                $isPubliable = false;

                if($parcours->getDpeParcours()?->last() instanceof DpeParcours){

                    $etatValidation = $parcours->getDpeParcours()?->last()->getEtatValidation();        
                    $count = count($parcours->getDpeParcours()?->last()->getEtatValidation()) ?? 0;

                    if($count > 0 && isset($etatValidation)){
                        $lastItem = array_keys($etatValidation)[$count - 1];
                        if($lastItem === 'valide_a_publier'){
                            $isPubliable = true;
                        }
                    }
                }

                if($isPubliable){
                    $tParcours[] = [
                        'id' => $parcours->getId(),
                        'libelle' => $parcours->getDisplay(),
                        'url' => $this->generateUrl('app_parcours_export_json_urca', ['parcours' => $parcours->getId()], UrlGenerator::ABSOLUTE_URL)
                    ];
                }
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
