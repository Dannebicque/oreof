<?php

namespace App\Service;

use App\Classes\CalculButStructureParcours;
use App\Classes\CalculStructureParcours;
use App\Classes\MyGotenbergPdf;
use App\DTO\StructureEc;
use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
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
                    foreach($ueEnfantData->getUeEnfants() as $ueEnfantDeuxiemeData){
                        $ueEnfantDeuxieme = $this->getUe($ueEnfantDeuxiemeData);
                        $this->copyDataForUe($ueEnfantDeuxieme, $parcours->getId());
                    }
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
        // Même nombre de semestres
        $result = $result && count($dto1->semestres) === count($dto2->semestres);
        for($i = 1; $i <= count($dto1->semestres); $i++){
            $result = $result && $this->compareSemestresHeures($dto1->semestres[$i], $dto2->semestres[$i]);
            // Même nombre d'UE
            $result = $result && count($dto1->semestres[$i]->ues) === count($dto2->semestres[$i]->ues);
            for($j = 1; $j <= count($dto1->semestres[$i]->ues); $j++){
                $result = $result && $this->compareTwoUeDTO($dto1->semestres[$i]->ues[$j], $dto2->semestres[$i]->ues[$j]);
                // Si des UE enfants
                if(count($dto1->semestres[$i]->ues[$j]->uesEnfants) > 0){
                    // Même nombre d'UE enfants
                    $result = $result && count($dto1->semestres[$i]->ues[$j]->uesEnfants()) 
                        === count($dto2->semestres[$i]->ues[$j]->uesEnfants());
                    for($k = 0; $k < count($dto1->semestres[$i]->ues[$j]->uesEnfants()); $k++){
                        $result = $result && 
                            $this->compareTwoUeDTO(
                                $dto1->semestres[$i]->ues[$j]->uesEnfants()[$k],
                                $dto2->semestres[$i]->ues[$j]->uesEnfants()[$k]
                            );
                        if(count($dto1->semestres[$i]->ues[$j]->uesEnfants()[$k]->uesEnfants()) > 0){
                            $result = $result && count($dto1->semestres[$i]->ues[$j]->uesEnfants()[$k]->uesEnfants())
                                === count($dto2->semestres[$i]->ues[$j]->uesEnfants()[$k]->uesEnfants());
                            for($l = 0; $l < count($dto1->semestres[$i]->ues[$j]->uesEnfants()[$k]->uesEnfants()); $l++){
                                $result = $result && $this->compareTwoUeDTO(
                                    $dto1->semestres[$i]->ues[$j]->uesEnfants()[$k]->uesEnfants()[$l],
                                    $dto2->semestres[$i]->ues[$j]->uesEnfants()[$k]->uesEnfants()[$l]
                                );
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function compareSemestresHeures(
        StructureSemestre $semestre1, 
        StructureSemestre $semestre2
    ) : bool {

        $result = true;
        /** 
         * Même nombre d'heures total sur le semestre
         */
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreTotalPres()
            === $semestre2->heuresEctsSemestre->sommeSemestreTotalPres();

        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreTotalDist() 
            === $semestre2->heuresEctsSemestre->sommeSemestreTotalDist();

        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreTotalPresDist() 
            === $semestre2->heuresEctsSemestre->sommeSemestreTotalPresDist();
        /**
         * Présentiel
         */
        // CM
        $result = $result && $semestre1->heuresEctsSemestre->sommeSemestreCmPres
            === $semestre2->heuresEctsSemestre->sommeSemestreCmPres;
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

    public function compareUeHeures(
        StructureUe $ue1,
        StructureUe $ue2
    ) : bool {

        $result = true;
        // Totaux
        $result = $result && $ue1->heuresEctsUe->sommeUeTotalPres() 
            === $ue2->heuresEctsUe->sommeUeTotalPres();

        $result = $result && $ue1->heuresEctsUe->sommeUeTotalDist() 
            === $ue2->heuresEctsUe->sommeUeTotalDist();   
            
        $result = $result && $ue1->heuresEctsUe->sommeUeTotalPresDist() 
            === $ue2->heuresEctsUe->sommeUeTotalPresDist();
        /**
         * Présentiel
         */
        // CM
        $result = $result && $ue1->heuresEctsUe->sommeUeCmPres 
            === $ue2->heuresEctsUe->sommeUeCmPres;
        // TD
        $result = $result && $ue1->heuresEctsUe->sommeUeTdPres 
            === $ue2->heuresEctsUe->sommeUeTdPres;
        // TP
        $result = $result && $ue1->heuresEctsUe->sommeUeTpPres 
            === $ue2->heuresEctsUe->sommeUeTpPres;
        /**
         * Distanciel
         */
        // CM
        $result = $result && $ue1->heuresEctsUe->sommeUeCmDist 
            === $ue2->heuresEctsUe->sommeUeCmDist;
        // TD
        $result = $result && $ue1->heuresEctsUe->sommeUeTdDist 
            === $ue2->heuresEctsUe->sommeUeTdDist;
        // TP
        $result = $result && $ue1->heuresEctsUe->sommeUeTpDist 
            === $ue2->heuresEctsUe->sommeUeTpDist;

        // TE
        $result = $result && $ue1->heuresEctsUe->sommeUeTePres 
            === $ue2->heuresEctsUe->sommeUeTePres;

        return $result;
    }

    public function compareEcHeures(
        StructureEc $ec1,
        StructureEc $ec2
    ) : bool {

        $result = true;
        // Totaux
        $result = $result && $ec1->heuresEctsEc->sommeEcTotalPres() 
            === $ec2->heuresEctsEc->sommeEcTotalPres();

        $result = $result && $ec1->heuresEctsEc->sommeEcTotalDist() 
            === $ec2->heuresEctsEc->sommeEcTotalDist();

        $result = $result && $ec1->heuresEctsEc->sommeEcTotalPresDist() 
            === $ec2->heuresEctsEc->sommeEcTotalPresDist();

        /**
         * Présentiel
         */
        // CM
        $result = $result && $ec1->heuresEctsEc->cmPres === $ec2->heuresEctsEc->cmPres;
        // TD
        $result = $result && $ec1->heuresEctsEc->tdPres === $ec2->heuresEctsEc->tdPres;
        // TP
        $result = $result && $ec1->heuresEctsEc->tpPres === $ec2->heuresEctsEc->tpPres;
        /**
         * Distanciel
         */
        // CM
        $result = $result && $ec1->heuresEctsEc->cmDist === $ec2->heuresEctsEc->cmDist;
        // TD
        $result = $result && $ec1->heuresEctsEc->tdDist === $ec2->heuresEctsEc->tdDist;
        // TP
        $result = $result && $ec1->heuresEctsEc->tpDist === $ec2->heuresEctsEc->tpDist;

        // TE
        $result = $result && $ec1->heuresEctsEc->tePres === $ec2->heuresEctsEc->tePres;

        return $result;
    }

    public function compareTwoUeDTO(
        StructureUe $ue1,
        StructureUe $ue2
    ) : bool {

        $result = true;
        // Même nombre d'EC
        $result = $result && count($ue1->elementConstitutifs) === count($ue2->elementConstitutifs);
        for($i = 0; $i < count($ue1->elementConstitutifs); $i++){
            // Comparaison d'heures des EC
            $result = $result && $this->compareEcHeures($ue1->elementConstitutifs[$i], $ue2->elementConstitutifs[$i]);
            if(count($ue1->elementConstitutifs[$i]->elementsConstitutifsEnfants) > 0){
                // Même nombre d'EC enfants
                $result = $result && count($ue1->elementConstitutifs[$i]->elementsConstitutifsEnfants) 
                    === count($ue2->elementConstitutifs[$i]->elementsConstitutifsEnfants);

                // Les EC enfants ont leur ID de BD comme clé
                $keys = array_keys($ue1->elementConstitutifs[$i]->elementsConstitutifsEnfants);    
                for($j = 0; $j < count($ue1->elementConstitutifs[$i]->elementsConstitutifsEnfants); $j++){
                    // Comparaison d'heures des EC enfants
                    $result = $result && $this->compareEcHeures(
                        $ue1->elementConstitutifs[$i]->elementsConstitutifsEnfants[$keys[$j]],
                        $ue2->elementConstitutifs[$i]->elementsConstitutifsEnfants[$keys[$j]]
                    );
                }
            }
        }

        return $result;
    }
}