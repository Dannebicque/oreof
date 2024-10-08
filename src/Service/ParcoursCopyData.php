<?php

namespace App\Service;

use App\Classes\CalculButStructureParcours;
use App\Classes\CalculStructureParcours;
use App\Classes\MyGotenbergPdf;
use App\Entity\ElementConstitutif;
use App\Entity\FicheMatiere;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\Ue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class ParcoursCopyData {

    private EntityManagerInterface $entityManager;

    private MyGotenbergPdf $myPdf;

    public function __construct(
        EntityManagerInterface $entityManager,
        MyGotenbergPdf $myPdf
    ){
        $this->entityManager = $entityManager;
        $this->myPdf = $myPdf;
    }

    public function copyDataForParcours(Parcours $parcours){
        foreach($parcours->getSemestreParcours() as $semestreParcours){
            $semestre = $this->getSemestre($semestreParcours->getSemestre());
            $ueArray = $this->entityManager->getRepository(Ue::class)->getBySemestre($semestre);
            foreach($ueArray as $ueData){
                $ue = $this->getUe($ueData);
                $this->copyDataForUe($ue);
                foreach($ueData->getUeEnfants() as $ueEnfantData){
                    $ueEnfant = $this->getUe($ueEnfantData);
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

    public function getDTOForParcours(
        Parcours $parcours, 
        bool $heuresSurFicheMatiere = false, 
        bool $withCopy = false
    ){
        if($parcours->getTypeDiplome()->getLibelleCourt() === 'BUT'){
            $calcul = new CalculButStructureParcours();
            $dto = $calcul->calcul($parcours);

            return $dto;
        }
        else {
            $ueRepository = $this->entityManager->getRepository(Ue::class);
            $ecRepository = $this->entityManager->getRepository(ElementConstitutif::class);
            $calcul = new CalculStructureParcours($this->entityManager, $ecRepository, $ueRepository);
            if($withCopy){
                $parcoursData = $parcours;
                $this->copyDataForParcours($parcoursData);
                $dto = $calcul->calcul($parcoursData, heuresSurFicheMatiere: $heuresSurFicheMatiere);
            }   
            else {
                $dto = $calcul->calcul($parcours, heuresSurFicheMatiere: $heuresSurFicheMatiere);
            }

            return $dto;
        }
    }

    private function getUe(Ue $ue){
        return $ue->getUeRaccrochee() !== null 
            ? $ue->getUeRaccrochee()->getUe()
            : $ue;
    }

    private function getSemestre(Semestre $semestre){
        return $semestre->getSemestreRaccroche() !== null 
            ? $semestre->getSemestreRaccroche()->getSemestre()
            : $semestre;
    }

    public function exportDTOAsPdf(
        Parcours $parcours, 
        bool $heuresSurFicheMatiere, 
        bool $withCopy = false
    ){
        return $this->myPdf->render(
            'typeDiplome/formation/_structure.html.twig',
            [
                'print' => true,
                'dto' => $this->getDTOForParcours($parcours, $heuresSurFicheMatiere, $withCopy),
                'parcours' => $parcours,
                'titre' => "Maquette-Parcours-{$parcours->getDisplay()}"
            ]
        );
    }

}