<?php

namespace App\Controller;

use App\Classes\GetHistorique;
use App\Entity\DpeParcours;
use App\Entity\Formation;
use App\Entity\ParcoursVersioning;
use App\Repository\FormationRepository;
use App\Service\ApiJsonExport;
use App\Service\VersioningParcours;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

        // COMPTEUR DES PARCOURS A ENVOYER
        $countParcours = 0;

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
                            ++$countParcours;
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

        // VERIFICATION NOMBRE DE PARCOURS ENVOYÉS
        // dump("Nombre de parcours envoyés : {$countParcours}.");exit;


        return new JsonResponse($data);
    }

    #[Route('/api/site/web/versioning_json/', name: 'api_site_web_versioning_json')]
    public function indexVersioningJson(
        ApiJsonExport $apiJsonExport,
        Filesystem $fs
    ) : Response {
        ini_set('memory_limit', '2500M');
        ini_set('max_execution_time', '300');

        $filename = "api_json_urca_versioning.json";
        $path = __DIR__ . "/../../public/api_json/";

        if($fs->exists($path . $filename) === true){
            $api = file_get_contents($path . $filename);
            return new JsonResponse($api, json: true);
        }

        $apiJson = $apiJsonExport->generateApiVersioning();
        $fs->appendToFile($path . $filename, json_encode($apiJson));

        return new JsonResponse($apiJson);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/site/web/versioning_json/renew')]
    public function apiVersioningJsonRenew(
        ApiJsonExport $apiJsonExport,
        Filesystem $fs
    ) : Response {
        ini_set('memory_limit', '2500M');
        ini_set('max_execution_time', '300');

        $filename = "api_json_urca_versioning.json";
        $path = __DIR__ . "/../../public/api_json/";

        if($fs->exists($path . $filename)){
            $now = (new DateTime())->format('d-m-Y_H-i');
            $fs->rename($path . $filename, $path . $now . "-" .  $filename);
            $apiJson = $apiJsonExport->generateApiVersioning();
            $fs->appendToFile($path . $filename, json_encode($apiJson));
        }

        return $this->redirectToRoute("api_site_web_versioning_json");
    }
}
