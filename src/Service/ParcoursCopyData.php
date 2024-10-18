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
use App\Entity\Formation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\Ue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class ParcoursCopyData {

    private EntityManagerInterface $entityManager;

    private MyGotenbergPdf $myPdf;

    public static array $errorMessageArray = [];

    public function __construct(
        ManagerRegistry $doctrine,
        MyGotenbergPdf $myPdf
    ){
        $this->entityManager = $doctrine->getManager('parcours_copy');
        $this->myPdf = $myPdf;
    }

    public function copyDataForAllParcoursInDatabase(SymfonyStyle $io){
        $io->writeln("Commande pour copier les heures des matières sur la nouvelle base de données.");

        $formationArray = $this->entityManager->getRepository(Formation::class)->findAll();
        $nombreParcours = array_sum(array_map(fn($f) => count($f->getParcours()), $formationArray));
        $io->writeln("Début de la copie...");
        $io->progressStart($nombreParcours);

        foreach($formationArray as $formation){
            foreach($formation->getParcours() as $parcours){
                $this->copyDataForParcoursFromDTO($parcours);
                $io->progressAdvance(1);
            }
        }
        $io->progressFinish();
        $io->success("La copie s'est exécutée avec succès !");
    }

    public function copyDataForParcours(Parcours $parcours){
        foreach($parcours->getSemestreParcours() as $semestreParcours){
            $semestre = $this->getSemestre($semestreParcours->getSemestre());
            if($semestre){
                $ueArray = $this->entityManager->getRepository(Ue::class)->getBySemestre($semestre);
                foreach($ueArray as $ueData){
                    $ue = $this->getUe($ueData);
                    $this->copyDataForUe($ue, $parcours->getId());
                    foreach($ue->getUeEnfants() as $ueEnfantData){
                        $ueEnfant = $this->getUe($ueEnfantData);
                        $this->copyDataForUe($ueEnfant, $parcours->getId());
                        foreach($ueEnfant->getUeEnfants() as $ueEnfantDeuxiemeData){
                            $ueEnfantDeuxieme = $this->getUe($ueEnfantDeuxiemeData);
                            $this->copyDataForUe($ueEnfantDeuxieme, $parcours->getId());
                        }
                    }
                }
            }
        }
    }

    public function copyDataForParcoursFromDTO(Parcours $parcours){
        $dto = $this->getDTOForParcours($parcours);
        foreach($dto->semestres as $semestre){
            foreach($semestre->ues as $ue){
                $this->copyDataForUeFromDTO($ue, $parcours->getId());
                foreach($ue->uesEnfants() as $ueEnfant){
                    $this->copyDataForUeFromDTO($ueEnfant, $parcours->getId());
                    foreach($ueEnfant->uesEnfants() as $ueEnfantDeuxieme){
                        $this->copyDataForUeFromDTO($ueEnfantDeuxieme, $parcours->getId());
                    }
                }
            }
        }
    }
    
    private function copyDataForUe(Ue $ue, int $parcoursId){
        $elementConstitutifArray = $this->entityManager->getRepository(ElementConstitutif::class)
            ->getByUe($ue);
        foreach($elementConstitutifArray as $ec){
            $this->copyDataOnFicheMatiere($ec, $ec->getFicheMatiere(), $parcoursId);
            foreach($ec->getEcEnfants() as $ecEnfant){
                $this->copyDataOnFicheMatiere($ecEnfant, $ecEnfant->getFicheMatiere(), $parcoursId);
            }
        }
    }

    private function copyDataForUeFromDTO(StructureUe $structUE, int $parcoursId){
        foreach($structUE->elementConstitutifs as $ec){
            $this->copyDataOnFicheMatiere($ec->elementConstitutif, $ec->elementConstitutif->getFicheMatiere(), $parcoursId);
            foreach($ec->elementsConstitutifsEnfants as $ecEnfant){
                $this->copyDataOnFicheMatiere($ecEnfant->elementConstitutif, $ecEnfant->elementConstitutif->getFicheMatiere(), $parcoursId);
            }
        }
    }

    private function copyDataOnFicheMatiere(
        ElementConstitutif $ecSource,
        ?FicheMatiere $ficheMatiereSource,
        int $parcoursId
    ){
        
        if($ficheMatiereSource){
            // $ficheMatiere = $this->entityManager->getRepository(FicheMatiere::class)
            //    ->findOneBySlug($ficheMatiereSource->getSlug());

            $isVolumeHoraireFMImpose = $ficheMatiereSource->isVolumesHorairesImpose();
            $ecFromParcours = $ecSource->getParcours()?->getId() === $ficheMatiereSource->getParcours()?->getId();
            $hasEcParentHeures = $ecSource->getEcParent()?->isHeuresEnfantsIdentiques();
            $hasSynchroHeures = $ecSource->isSynchroHeures();
            $isHorsDiplome = $ficheMatiereSource->isHorsDiplome();

            if($ecFromParcours){
                $ec = $ecSource;
            }

            if(!$isVolumeHoraireFMImpose && !$isHorsDiplome){
                if($hasEcParentHeures){
                    $ec = $ecSource->getEcParent();
                }
                elseif($hasSynchroHeures){
                    $ecPorteur = array_filter(
                        $ficheMatiereSource->getElementConstitutifs()->toArray(), 
                        fn($ec) => $ec->getParcours()->getId() === $ficheMatiereSource->getParcours()->getId());

                    if(count($ecPorteur) > 0){
                        $ec = array_shift($ecPorteur);
                    }

                }
                elseif($ecFromParcours === false && $this->hasEcSameHeuresAsFicheMatiere($ecSource, $ficheMatiereSource) === false) {
                    $ecSource->setHeuresSpecifiques(true);
                    $this->entityManager->persist($ecSource);
                }

                if($ec){
                    $ficheMatiereSource->setVolumeCmPresentiel($ec->getVolumeCmPresentiel());
                    $ficheMatiereSource->setVolumeTdPresentiel($ec->getVolumeTdPresentiel());
                    $ficheMatiereSource->setVolumeTpPresentiel($ec->getVolumeTpPresentiel());
                    $ficheMatiereSource->setVolumeCmDistanciel($ec->getVolumeCmDistanciel());
                    $ficheMatiereSource->setVolumeTdDistanciel($ec->getVolumeTdDistanciel());
                    $ficheMatiereSource->setVolumeTpDistanciel($ec->getVolumeTpDistanciel());
                    $ficheMatiereSource->setVolumeTe($ec->getVolumeTe());

                }
                
                $this->entityManager->persist($ficheMatiereSource);
                $this->entityManager->flush();
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

    private function getSemestre(?Semestre $semestre){
        if($semestre){
            return $semestre->getSemestreRaccroche() !== null 
                ? $semestre->getSemestreRaccroche()->getSemestre()
                : $semestre;
        }
        return null;
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
        $nbSemestre = count($dto1->semestres) === count($dto2->semestres);
        if($nbSemestre === false){
            self::$errorMessageArray[] = "Nombre de semestres différents.";
            $result = false;
        }
        foreach($dto1->semestres as $indexSemestre => $semestre){
            $totalHeureSemestre = $this->compareSemestresHeures($dto1->semestres[$indexSemestre], $dto2->semestres[$indexSemestre]);
            if($totalHeureSemestre === false){
                $result = false;
            }
            // Même nombre d'UE
            $nbUe = count($dto1->semestres[$indexSemestre]->ues) === count($dto2->semestres[$indexSemestre]->ues);
            if($nbUe === false){
                self::$errorMessageArray[] = "S{$indexSemestre} : nombre d'UE différent";
                $result = false;
            }
            foreach($dto1->semestres[$indexSemestre]->ues as $indexUe => $ue){
                // Comparaison des heures des UE
                $comparaisonUe = $this->compareTwoUeDTO(
                    $dto1->semestres[$indexSemestre]->ues[$indexUe],
                    $dto2->semestres[$indexSemestre]->ues[$indexUe]
                );
                if($comparaisonUe === false){
                    $result = false;
                }
                // Si des UE enfants
                if(count($dto1->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants) > 0){
                    // Même nombre d'UE enfants
                    $nbUeEnfant = count($dto1->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants()) 
                        === count($dto2->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants());
                    if($nbUeEnfant === false){
                        $result = false;
                        self::$errorMessageArray[] = "S{$indexSemestre} - {$ue->display} : nombre d'enfant différent";
                    }

                    foreach($dto1->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants() as $indexUeE => $ueE){
                        // Comparaison des heures des UE Enfant
                        $comparaisonUeEnfant = $this->compareTwoUeDTO(
                                $dto1->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants()[$indexUeE],
                                $dto2->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants()[$indexUeE]
                            );
                        if($comparaisonUeEnfant === false){
                            $result = false;
                        }
                        // Même nombre d'UE Enfant
                        if(count($dto1->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants()[$indexUeE]->uesEnfants()) > 0){
                            $nbUeEnfantDeuxieme = count($dto1->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants()[$indexUeE]->uesEnfants())
                                === count($dto2->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants()[$indexUeE]->uesEnfants());
                            if($nbUeEnfantDeuxieme === false){
                                $result = false;
                                self::$errorMessageArray[] = "S{$indexSemestre} - {$ue->display} : nombre d'enfant différent";
                            }
                            // Comparaison des heures des UE enfant d'UE enfant
                            foreach($dto1->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants()[$indexUeE]->uesEnfants() as $indexUeEDeuxieme => $ueEDeuxieme){
                                $comparaisonUeEnfantDeuxieme = $this->compareTwoUeDTO(
                                    $dto1->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants()[$indexUeE]->uesEnfants()[$indexUeEDeuxieme],
                                    $dto2->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants()[$indexUeE]->uesEnfants()[$indexUeEDeuxieme]
                                );
                                if($comparaisonUeEnfantDeuxieme === false){
                                    $result = false;
                                }
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

        if($result === false){
            self::$errorMessageArray[] = "Semestre {$semestre1->semestre->getOrdre()} - le total d'heure du semestre est différent";
        }

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

        if($result === false){
            self::$errorMessageArray[] = "{$ue1->display} - le total d'heure de l'UE est différent";
        }

        return $result;
    }

    public function compareEcHeures(
        StructureEc $ec1,
        StructureEc $ec2,
        string $ueDisplay = ""
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

        if($result === false){
            self::$errorMessageArray[] = "Les deux EC ne correspondent pas. ({$ueDisplay} - {$ec1->elementConstitutif->getCode()})";
        }

        return $result;
    }

    public function compareTwoUeDTO(
        StructureUe $ue1,
        StructureUe $ue2
    ) : bool {

        $result = true;
        // Même heures sur les UE
        $totalHeureUe = $this->compareUeHeures($ue1, $ue2);
        // Même nombre d'EC
        $nbEc = count($ue1->elementConstitutifs) === count($ue2->elementConstitutifs);
        if($totalHeureUe === false){
            $result = false;
        }
        if($nbEc === false){
            self::$errorMessageArray[] = "{$ue1->display} : Nombre d'EC différent";
            $result = false;
        }
        foreach($ue1->elementConstitutifs as $indexEc => $valueEc){
            // Comparaison d'heures des EC
            $comparaisonHeureEc = $this->compareEcHeures(
                $ue1->elementConstitutifs[$indexEc],
                $ue2->elementConstitutifs[$indexEc], 
                $ue1->display
            );
            if($comparaisonHeureEc === false){
                $result = false;
            }
            if(count($ue1->elementConstitutifs[$indexEc]->elementsConstitutifsEnfants) > 0){
                // Même nombre d'EC enfants
                $nbEcEnfant = count($ue1->elementConstitutifs[$indexEc]->elementsConstitutifsEnfants) 
                    === count($ue2->elementConstitutifs[$indexEc]->elementsConstitutifsEnfants);
                if($nbEcEnfant === false){
                    self::$errorMessageArray[] = "{$ue1->display} - nombre d'EC enfants différent";
                    $result = false;
                }
                // Les EC enfants ont leur ID de BD comme clé
                foreach($ue1->elementConstitutifs[$indexEc]->elementsConstitutifsEnfants as $indexEcE => $valueEcE){
                    // Comparaison d'heures des EC enfants
                    $comparaisonHeureEcEnfant = $this->compareEcHeures(
                        $ue1->elementConstitutifs[$indexEc]->elementsConstitutifsEnfants[$indexEcE],
                        $ue2->elementConstitutifs[$indexEc]->elementsConstitutifsEnfants[$indexEcE],
                        $ue1->display
                    );
                    if($comparaisonHeureEcEnfant === false){
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }

    public function hasEcSameHeuresAsFicheMatiere(
        ElementConstitutif $ec, 
        FicheMatiere $ficheMatiere
    ){  
        return $ficheMatiere->getVolumeCmPresentiel() ?? 0 === $ec->getVolumeCmPresentiel() ?? 0
        && $ficheMatiere->getVolumeCmDistanciel() ?? 0 === $ec->getVolumeCmDistanciel() ?? 0
        && $ficheMatiere->getVolumeTdPresentiel() ?? 0 === $ec->getVolumeTdPresentiel() ?? 0
        && $ficheMatiere->getVolumeTdDistanciel() ?? 0 === $ec->getVolumeTdDistanciel() ?? 0
        && $ficheMatiere->getVolumeTpPresentiel() ?? 0 === $ec->getVolumeTpPresentiel() ?? 0
        && $ficheMatiere->getVolumeTpDistanciel() ?? 0 === $ec->getVolumeTpDistanciel() ?? 0
        && $ficheMatiere->getVolumeTe() ?? 0 === $ec->getVolumeTe() ?? 0;
    }
}