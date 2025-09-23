<?php

namespace App\Service;

use App\Classes\GetHistorique;
use App\Entity\DpeParcours;
use App\Entity\Formation;
use App\Entity\ParcoursVersioning;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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

    public function generateApiVersioning(
        string $hostname,
        SymfonyStyle $io = null
    ): array
    {

        $dateValidation = new DateTime('2025-09-21 00:00:00');
        $dateValidation = $dateValidation->format('Y-m-d H:i:s');

        $dataJSON = [];
        $formationArray = $this->entityManager->getRepository(Formation::class)->findAll();
        $urlPrefix = "https://" . $hostname;

        /**
         * Filtrage sur la campagne de collecte en cours
         */
        $formationArray = array_filter($formationArray, function($f){
            $parcoursCampagneActuelle = array_filter($f->getParcours()->toArray(),
                function($p) {
                    $dpeParcours = $p->getDpeParcours()?->last();
                    if($dpeParcours instanceof DpeParcours){
                        return $dpeParcours->getCampagneCollecte()?->getId() === 2;
                    }
                    return false;
                }
            );
            return count($parcoursCampagneActuelle) > 0;
        });

        $countParcours = 0;
        $countProgress = count($formationArray);

        $io?->progressStart($countProgress);

        foreach($formationArray as $formation){
            $dateValidationFormation = [];
            $tParcours = [];
            foreach($formation->getParcours() as $parcours){
                $lastVersion = $this->entityManager->getRepository(ParcoursVersioning::class)
                    ->findLastCfvuVersion($parcours);
                if(count($lastVersion) > 0){
                    $lastVersionData = $this->versioningParcours->loadParcoursFromVersion($lastVersion[0]);
                    $tParcours[] = [
                        'id' => $parcours->getId(),
                        'libelle' => $lastVersionData['parcours']->getDisplay(),
                        'url' => $urlPrefix . $this->router->generate(
                                'app_parcours_export_json_urca_cfvu_valid',
                                ['parcours' => $lastVersion[0]->getParcours()->getId()]
                        )
                    ];
                    $dateValideCfvu = $this->getHistorique
                        ->getHistoriqueParcoursLastStep($parcours->getDpeParcours()->last(), 'valide_cfvu')
                        ?->getDate();

                    $dateValideAPublier = $this->getHistorique
                        ->getHistoriqueParcoursLastStep($parcours->getDpeParcours()->last(), 'valide_a_publier')
                        ?->getDate();

                    if($dateValideCfvu !== null){
                        $dateValidationFormation[] = $dateValideCfvu;
                    }
                    if($dateValideAPublier !== null){
                        $dateValidationFormation[] = $dateValideAPublier;
                    }
                    ++$countParcours;

                }
            }
            // Date de validation : la plus récente des dates de publication de parcours
            if(count($dateValidationFormation) > 0){
                rsort($dateValidationFormation);
                $dateValidationFormation = $dateValidationFormation[0];
            }else {
                $dateValidationFormation = null;
            }

            if(count($tParcours) > 0){
                $dataJSON[] = [
                    'id' => $formation->getId(),
                    'libelle' => $formation->getDisplayLong(),
                    'parcours' => $tParcours,
                    'dateValidation' => $dateValidation
                    
                    // Date de validation calculée
                    // $dateValidationFormation?->format('Y-m-d H:i:s') ?? null
                ];
            }

            $io?->progressAdvance();
        }

        $io?->progressFinish();

        $io?->writeln($countParcours . ' Parcours ont été ajoutés à l\'API');

        return $dataJSON;
    }
}
