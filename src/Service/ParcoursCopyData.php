<?php

namespace App\Service;

use App\Classes\CalculButStructureParcours;
use App\Classes\CalculStructureParcours;
use App\Classes\MyGotenbergPdf;
use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
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
                $this->copyDataForUe($ue, $parcours->getId());
                foreach($ueData->getUeEnfants() as $ueEnfantData){
                    $ueEnfant = $this->getUe($ueEnfantData);
                    $this->copyDataForUe($ueEnfant, $parcours->getId());
                }
            }
        }
    }
    
    private function copyDataForUe(Ue $ue, int $parcoursId){
        foreach($ue->getElementConstitutifs() as $ec){
            $this->copyDataOnFicheMatiere($ec, $ec->getFicheMatiere(), $parcoursId);
            foreach($ec->getEcEnfants() as $ecEnfant){
                $this->copyDataOnFicheMatiere($ecEnfant, $ecEnfant->getFicheMatiere(), $parcoursId);
            }
        }
    }

    private function copyDataOnFicheMatiere(
        ElementConstitutif $ecSource,
        ?FicheMatiere $ficheMatiere,
        int $parcoursId
    ){
        
        if($ficheMatiere){
            $isVolumeHoraireFMImpose = $ficheMatiere->isVolumesHorairesImpose();
            if(!$isVolumeHoraireFMImpose){
                $ficheMatiereBD = $this->entityManager->getRepository(FicheMatiere::class)
                    ->findOneById($ficheMatiere->getId());
    
                $ec = $ecSource;
                if($ecSource->getEcParent()?->isHeuresEnfantsIdentiques()){
                    $ec = $ecSource->getEcParent();
                }
                if($ficheMatiereBD->getParcours()->getId() !== $parcoursId){
                    foreach($ficheMatiereBD->getElementConstitutifs() as $ecFM){
                        if($ecFM->getParcours()->getId() === $ficheMatiereBD->getParcours()->getId()){
                            $ec = $ecFM;
                        }
                    }
                }

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

    public function compareTwoDTO(StructureParcours $dto1, StructureParcours $dto2){
        $result = true;
        $result = $result && count($dto1->semestres) === count($dto2->semestres);
        for($i = 1; $i <= count($dto1->semestres); $i++){
            $result = $result && $this->compareTwoSemestresDTO($dto1->semestres[$i], $dto2->semestres[$i]);
        }

        return $result;
    }

    public function compareTwoSemestresDTO(
        StructureSemestre $semestre1, 
        StructureSemestre $semestre2
    ) : bool {
        $result = true;
        /** 
         * Même nombre d'heures total sur le semestre
         */
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreTotalDist() 
            === $semestre2->heuresEctsSemestre->sommeSemestreTotalDist();
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreTotalPres()
            === $semestre2->heuresEctsSemestre->sommeSemestreTotalPres();
        /**
         * Présentiel
         */
        // CM
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreCmPres
            === $semestre1->heuresEctsSemestre->sommeSemestreCmPres;
        // TD
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreTdPres 
            === $semestre2->heuresEctsSemestre->sommeSemestreTdPres;
        // TP
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreTpPres 
            === $semestre2->heuresEctsSemestre->sommeSemestreTpPres;
        /**
         * Distanciel
         */
        // CM
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreCmDist 
            === $semestre2->heuresEctsSemestre->sommeSemestreCmDist;
        // TD
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreTdDist 
            === $semestre2->heuresEctsSemestre->sommeSemestreTdDist;
        //TP
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreTpDist 
            === $semestre2->heuresEctsSemestre->sommeSemestreTpDist;

        // TE
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreTePres 
            === $semestre2->heuresEctsSemestre->sommeSemestreTePres;

        return $result;
    }

}