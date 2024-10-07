<?php

namespace App\Service;

use App\Classes\CalculButStructureParcours;
use App\Classes\CalculStructureParcours;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\Ue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class ParcoursCopyData {

    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ){
        $this->entityManager = $entityManager;
    }

    public function copyDataForParcours(Parcours $parcours){
        foreach($parcours->getSemestreParcours() as $semestreParcours){
            foreach($semestreParcours->getSemestre()->getUes() as $ue){
                $this->copyDataForUe($ue);
                foreach($ue->getUeEnfants() as $ueEnfant){
                    $this->copyDataForUe($ueEnfant);
                }
            }
        }
    }
    
    private function copyDataForUe(Ue $ue){
        foreach($ue->getElementConstitutifs() as $ec){
            $this->copyDataOnFicheMatiere($ec, $ec->getFicheMatiere());
            foreach($ec->getEcEnfants() as $ecEnfant){
                $this->copyDataOnFicheMatiere($ecEnfant, $ecEnfant->getFicheMatiere());
            }
        }
    }

    private function copyDataOnFicheMatiere(ElementConstitutif $ec, ?FicheMatiere $ficheMatiere){
        
        if($ficheMatiere){
            $isVolumeHoraireFMImpose = $ficheMatiere->isVolumesHorairesImpose();

            if(!$isVolumeHoraireFMImpose){
                $ficheMatiereBD = $this->entityManager->getRepository(FicheMatiere::class)
                    ->findOneById($ficheMatiere->getId());
    
                $ficheMatiereBD->setVolumeCmPresentiel($ec->getVolumeCmPresentiel());
                $ficheMatiereBD->setVolumeTdPresentiel($ec->getVolumeTdPresentiel());
                $ficheMatiereBD->setVolumeTpPresentiel($ec->getVolumeTpPresentiel());
                $ficheMatiereBD->setVolumeCmDistanciel($ec->getVolumeCmDistanciel());
                $ficheMatiereBD->setVolumeTdDistanciel($ec->getVolumeTdDistanciel());
                $ficheMatiereBD->setVolumeTpDistanciel($ec->getVolumeTpDistanciel());
                $ficheMatiereBD->setVolumeTe($ec->getVolumeTe());
    
                $this->entityManager->persist($ficheMatiereBD);
            }
        }
    }

    public function getDTOForParcours(Parcours $parcours){
        if($parcours->getTypeDiplome()->getLibelleCourt() === 'BUT'){
            $calcul = new CalculButStructureParcours();
            $dto = $calcul->calcul($parcours);

            return $dto;
        }
        else {
            $ueRepository = $this->entityManager->getRepository(Ue::class);
            $ecRepository = $this->entityManager->getRepository(ElementConstitutif::class);
            $calcul = new CalculStructureParcours($this->entityManager, $ecRepository, $ueRepository);
            $dto = $calcul->calcul($parcours);

            return $dto;
        }
    }

}