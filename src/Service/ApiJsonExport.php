<?php

namespace App\Service;

use App\Classes\GetHistorique;
use App\Entity\Formation;
use App\Entity\ParcoursVersioning;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiJsonExport {

    private EntityManagerInterface $entityManager;

    private GetHistorique $getHistorique;

    private VersioningParcours $versioningParcours;

    private UrlGeneratorInterface $router;

    public function __construct(
        EntityManagerInterface $entityManager,
        GetHistorique $getHistorique,
        VersioningParcours $versioningParcours,
        UrlGeneratorInterface $router
    ){
        $this->entityManager = $entityManager;
        $this->getHistorique = $getHistorique;
        $this->versioningParcours = $versioningParcours;
        $this->router = $router;
    }

    public function generateApiVersioning(){
        $dataJSON = [];
        $formationArray = $this->entityManager->getRepository(Formation::class)->findAll();
        $countParcours = 0;

        foreach($formationArray as $formation){
            $tParcours = [];
            foreach($formation->getParcours() as $parcours){
                $lastVersion = $this->entityManager->getRepository(ParcoursVersioning::class)
                    ->findLastCfvuVersion($parcours);
                if(count($lastVersion) > 0){
                    $lastVersionData = $this->versioningParcours->loadParcoursFromVersion($lastVersion[0]);
                    $tParcours[] = [
                        'id' => $parcours->getId(),
                        'libelle' => $lastVersionData['parcours']->getDisplay(),
                        'url' => $this->router->generate(
                            'app_parcours_export_json_urca_cfvu_valid', 
                            ['parcours' => $lastVersion[0]->getParcours()->getId()], 
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ];
                    ++$countParcours;
                }
            }
            $dataJSON[] = [
                'id' => $formation->getId(),
                'libelle' => $formation->getDisplayLong(),
                'parcours' => $tParcours,
                'dateValidation' => $this->getHistorique
                    ->getHistoriqueFormationLastStep($formation, 'publication')
                    ?->getDate()
                    ?->format('Y-m-d H:i:s') ?? null,
            ];
        }

        return $dataJSON;
    }
}