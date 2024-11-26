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
use App\Entity\Mccc;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\Ue;
use App\Repository\ElementConstitutifCopyRepository;
use App\Repository\FicheMatiereCopyRepository;
use App\Repository\McccCopyRepository;
use App\Repository\UeCopyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class ParcoursCopyData {

    private EntityManagerInterface $entityManager;

    private EntityManagerInterface $entityManagerCopy;

    private ElementConstitutifCopyRepository $ecCopyRepo;

    private FicheMatiereCopyRepository $fmCopyRepo;

    private McccCopyRepository $mcccCopyRepo;

    private MyGotenbergPdf $myPdf;

    public static array $errorMessageArray = [];

    public static $errorMcccMessageArray = [];

    private array $ficheMatiereCopyDataArray = [];

    private array $mcccCopyDataArray = [];


    public function __construct(
        ManagerRegistry $doctrine,
        MyGotenbergPdf $myPdf
    ){
        $this->entityManager = $doctrine->getManager('default');
        $this->entityManagerCopy = $doctrine->getManager('parcours_copy');
        $this->ecCopyRepo = new ElementConstitutifCopyRepository($this->entityManagerCopy, ElementConstitutif::class);
        $this->fmCopyRepo = new FicheMatiereCopyRepository($this->entityManagerCopy, FicheMatiere::class);
        $this->mcccCopyRepo = new McccCopyRepository($this->entityManagerCopy, Mccc::class);

        $this->myPdf = $myPdf;
    }

    public function copyDataForAllParcoursInDatabase(SymfonyStyle $io){
        $io->writeln("Commande pour copier les heures des matières sur la nouvelle base de données.");

        $formationArray = $this->entityManager->getRepository(Formation::class)->findAll();
        $nombreParcours = array_sum(
            array_map(fn($f) => count($f->getParcours()), 
                array_filter($formationArray, fn($f) => $f->getTypeDiplome()->getLibelleCourt() !== "BUT")
            )
        );
        $io->writeln("Début de la copie des heures sur les fiches matières...");
        $io->progressStart($nombreParcours);

        foreach($formationArray as $formation){
            if($formation->getTypeDiplome()->getLibelleCourt() !== "BUT"){
                foreach($formation->getParcours() as $parcours){
                    $this->copyDataForParcoursFromDTO($parcours);
                    $io->progressAdvance(1);
                }
            }
        }
        $io->progressFinish();

        $io->writeln("Deuxième parcours de la base de données pour les heures spécifiques...");
        $io->progressStart($nombreParcours);
        foreach($formationArray as $f){
            if($f->getTypeDiplome()->getLibelleCourt() !== "BUT"){
                foreach($f->getParcours() as $p){
                    $this->copyDataForParcoursFromDTO($p, onlyHeuresSpecifiques: true);
                    $io->progressAdvance(1);
                }
            }
        }
        $io->progressFinish();

        $io->writeln("Traitement de la copie des MCCC...");;
        $io->progressStart($nombreParcours);
        foreach($formationArray as $f){
            if($f->getTypeDiplome()->getLibelleCourt() !== "BUT"){
                foreach($f->getParcours() as $parcours){
                    $this->copyDataForParcoursFromDTO($parcours, onlyMccc: true);
                    $io->progressAdvance(1);
                }
            }
        }
        $io->progressFinish();

        $io->writeln("Application des changements...");
        $this->entityManagerCopy->flush();

        $io->writeln("Second parcours pour les MCCC spécifiques...");
        $io->progressStart($nombreParcours);
        foreach($formationArray as $f){
            if($f->getTypeDiplome()->getLibelleCourt() !== "BUT"){
                foreach($f->getParcours() as $parcours){
                    $this->copyDataForParcoursFromDTO($parcours, onlyEcMcccSpecifiques: true);
                    $io->progressAdvance(1);
                }
            }
        }
        $io->progressFinish();

        $io->writeln("Application des changements...");
        $this->entityManagerCopy->flush();
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

    public function copyDataForParcoursFromDTO(
        Parcours $parcours, 
        bool $onlyHeuresSpecifiques = false,
        bool $onlyMccc = false,
        bool $onlyEcMcccSpecifiques = false
    ){
        $dto = $this->getDTOForParcours($parcours);
        foreach($dto->semestres as $semestre){
            foreach($semestre->ues as $ue){
                $this->copyDataForUeFromDTO($ue, $parcours->getId(), $onlyHeuresSpecifiques, $onlyMccc, $onlyEcMcccSpecifiques);
                foreach($ue->uesEnfants() as $ueEnfant){
                    $this->copyDataForUeFromDTO($ueEnfant, $parcours->getId(), $onlyHeuresSpecifiques, $onlyMccc, $onlyEcMcccSpecifiques);
                    foreach($ueEnfant->uesEnfants() as $ueEnfantDeuxieme){
                        $this->copyDataForUeFromDTO($ueEnfantDeuxieme, $parcours->getId(), $onlyHeuresSpecifiques, $onlyMccc, $onlyEcMcccSpecifiques);
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

    private function copyDataForUeFromDTO(
        StructureUe $structUE, 
        int $parcoursId, 
        bool $onlyHeuresSpecifiques,
        bool $onlyMccc,
        bool $onlyEcMcccSpecifiques,
    ){
        foreach($structUE->elementConstitutifs as $ec){
            if($onlyHeuresSpecifiques){
                $this->placeHeuresSpecifiquesFlag($ec->elementConstitutif);
            }
            elseif($onlyMccc){
                $this->moveMcccToFicheMatiere($ec, $parcoursId);
            } elseif($onlyEcMcccSpecifiques){
                $this->placeMcccSpecifiquesFlag($ec);
            } else {
                $this->copyDataOnFicheMatiere($ec->elementConstitutif, $ec->elementConstitutif->getFicheMatiere(), $parcoursId);
            }
            foreach($ec->elementsConstitutifsEnfants as $ecEnfant){
                if($onlyHeuresSpecifiques){
                    $isHeuresIdentiques = $ec->elementConstitutif->isHeuresEnfantsIdentiques();
                    $this->placeHeuresSpecifiquesFlag($ecEnfant->elementConstitutif, $isHeuresIdentiques);
                }elseif($onlyMccc){
                    $this->moveMcccToFicheMatiere($ecEnfant, $parcoursId);
                }elseif($onlyEcMcccSpecifiques){
                    $this->placeMcccSpecifiquesFlag($ecEnfant);
                }else {
                    if($ec->elementConstitutif->isHeuresEnfantsIdentiques()){
                        $this->copyDataOnFicheMatiere(
                            $ec->elementConstitutif, 
                            $ecEnfant->elementConstitutif->getFicheMatiere(),
                            $parcoursId,
                            isHeuresEnfantIdentiques: true
                        );
                    }else {
                        $this->copyDataOnFicheMatiere($ecEnfant->elementConstitutif, $ecEnfant->elementConstitutif->getFicheMatiere(), $parcoursId);
                    }
                }
            }
        }
    }

    private function copyDataOnFicheMatiere(
        ElementConstitutif $ecSource,
        ?FicheMatiere $ficheMatiereSource,
        int $parcoursId,
        bool $isHeuresEnfantIdentiques = false
    ){
        
        if($ficheMatiereSource){
            $isVolumeHoraireFMImpose = $ficheMatiereSource->isVolumesHorairesImpose();
            $ecFromParcours = $ecSource->getParcours()?->getId() === $ficheMatiereSource->getParcours()?->getId();
            $ficheMatiereFromParcours = $ficheMatiereSource->getParcours()?->getId() === $parcoursId;
            // $hasEcParentHeures = $ecSource->getEcParent()?->isHeuresEnfantsIdentiques();
            $hasSynchroHeures = $ecSource->isSynchroHeures();
            $isHorsDiplome = $ficheMatiereSource->isHorsDiplome();
            // Si la fiche matière a un EC porteur (parcours de la fiche matière = parcours de l'EC) 
            $hasFicheMatiereEcPorteur = array_filter(
                $ficheMatiereSource->getElementConstitutifs()->toArray(), 
                fn($ecFM) => $ecFM->getParcours()?->getId() === $ficheMatiereSource->getParcours()?->getId()
                    && $ecFM->getParcours()?->getId() !== null && $ficheMatiereSource->getParcours()?->getId() !== null
            );
            $hasFicheMatiereEcPorteur = count($hasFicheMatiereEcPorteur) > 0;
            $countEcForFiche = count($ficheMatiereSource->getElementConstitutifs()->toArray());

            $isEcPorteur = false;
            // Si l'EC et la FM font partie du parcours
            if($ficheMatiereFromParcours && $ecFromParcours){
                $ec = $ecSource;
                $isEcPorteur = true;
            }
            // Si la fiche n'a pas d'EC porteur, on prend le premier
            if($hasFicheMatiereEcPorteur === false || $countEcForFiche === 1){
                $ec = $ficheMatiereSource->getElementConstitutifs()->first();
            }
            // Si l'EC fait partie d'une UE mutualisée (portée par un autre parcours)
            if($ecSource->getUe()?->getUeMutualisables()->count() > 0){
                if($ecSource->getUe()->getSemestre()->getSemestreParcours()->first()->getParcours()->getId() === $parcoursId){
                    $ec = $ecSource;
                    $isEcPorteur = true;
                }
            }

            // Si le volume est imposé ou que la FM est hors diplôme, les heures sont déjà dessus
            if(!$isVolumeHoraireFMImpose && !$isHorsDiplome){
                // Cas où il y a la valeur 'synchro heures'
                if($hasSynchroHeures){
                    if(count($ficheMatiereSource->getElementConstitutifs()->toArray()) >= 2){
                        $ecPorteur = $ficheMatiereSource->getElementConstitutifs()->filter(
                            fn($ec) => $ec->getParcours()->getId() === $ficheMatiereSource->getParcours()->getId()
                                && $ec->getParcours()->getId() !== null && $ficheMatiereSource->getParcours()->getId() !== null
                        );
                        if(count($ecPorteur) > 0){
                            $ec = $ecPorteur->first();
                        }
                    }elseif(count($ficheMatiereSource->getElementConstitutifs()->toArray()) === 1){
                        $ec = $ficheMatiereSource->getElementConstitutifs()->first();
                    }
                }
                // Cas où il y a la valeur 'heure enfant identique'
                if($isHeuresEnfantIdentiques && $ficheMatiereFromParcours && $ecFromParcours){
                    $ec = $ecSource;
                }  
                if(($isEcPorteur || $hasFicheMatiereEcPorteur === false || $countEcForFiche === 1) 
                    && $this->hasHeuresFicheMatiereCopy($ficheMatiereSource) === false
                ) {
                    $ficheMatiereFromCopy = $this->fmCopyRepo->find($ficheMatiereSource->getId());

                    $ficheMatiereFromCopy->setVolumeCmPresentiel($ec->getVolumeCmPresentiel());
                    $ficheMatiereFromCopy->setVolumeTdPresentiel($ec->getVolumeTdPresentiel());
                    $ficheMatiereFromCopy->setVolumeTpPresentiel($ec->getVolumeTpPresentiel());
                    $ficheMatiereFromCopy->setVolumeCmDistanciel($ec->getVolumeCmDistanciel());
                    $ficheMatiereFromCopy->setVolumeTdDistanciel($ec->getVolumeTdDistanciel());
                    $ficheMatiereFromCopy->setVolumeTpDistanciel($ec->getVolumeTpDistanciel());
                    $ficheMatiereFromCopy->setVolumeTe($ec->getVolumeTe());

                    $this->ficheMatiereCopyDataArray[$ficheMatiereSource->getId()] = [
                        'cmPres' => $ec->getVolumeCmPresentiel(),
                        'tdPres' => $ec->getVolumeTdPresentiel(),
                        'tpPres' => $ec->getVolumeTpPresentiel(),
                        'cmDist' => $ec->getVolumeCmDistanciel(),
                        'tdDist' => $ec->getVolumeTdDistanciel(),
                        'tpDist' => $ec->getVolumeTpDistanciel(),
                        'te' => $ec->getVolumeTe(),
                    ];

                    $this->entityManagerCopy->persist($ficheMatiereFromCopy);
                }       
            }
        }
    }

    public function placeHeuresSpecifiquesFlag(ElementConstitutif $ec, bool $isHeuresIdentiques = false){
        if($ec->getFicheMatiere()){
            $ecSource = $ec;
            if($isHeuresIdentiques){
                $ecSource = $ec->getEcParent();
            }
            $isDifferent = $this->hasHeuresFicheMatiereCopy($ec->getFicheMatiere())
                && $this->hasEcSameHeuresAsFicheMatiereCopy($ecSource, $ec->getFicheMatiere()) === false;
    
            if($isDifferent && ($ec->isSynchroHeures() === false || $ec->isSansHeure())){
                $ecCopyFlag = $this->ecCopyRepo->find($ec->getId());
                $ecCopyFlag->setHeuresSpecifiques(true);
                $this->entityManagerCopy->persist($ecCopyFlag);
            }
        }
    }

    public function moveMcccToFicheMatiere(
        StructureEc $structEc,
        int $parcoursId
    ){
        $ficheMatierePorteuse = null;

        $ecFromParcours = $structEc->elementConstitutif->getParcours()?->getId() 
            === $structEc->elementConstitutif->getFicheMatiere()?->getParcours()?->getId();

        // Si c'est l'EC porteur
        $isEcPorteur = false;
        if($ecFromParcours && $structEc->elementConstitutif->getParcours()->getId() === $parcoursId){
            $isEcPorteur = true;
            $ficheMatierePorteuse = $structEc->elementConstitutif->getFicheMatiere();
        }

        $isEcOnlyOne = false;
        // Si la fiche matière n'est liée qu'à un seul EC
        if($ecFromFiche = $structEc->elementConstitutif->getFicheMatiere()?->getElementConstitutifs()){
            if(count($ecFromFiche) === 1 && !$isEcPorteur){
                $ficheMatierePorteuse = $structEc->elementConstitutif->getFicheMatiere();
                $isEcOnlyOne = true;
            }
        }

        // Si l'EC fait partie d'une UE mutualisée (porté par un autre parcours)
        if($structEc->elementConstitutif->getUe()?->getUeMutualisables()->count() > 0){
            if($structEc->elementConstitutif->getUe()->getSemestre()->getSemestreParcours()
                ->first()->getParcours()->getId() === $parcoursId
            ){
                $ficheMatierePorteuse = $structEc->elementConstitutif->getFicheMatiere();
                $isEcPorteur = true;
            }
        }

        // Si les MCCC sont sur l'EC
        if(!$structEc->elementConstitutif->getFicheMatiere()?->isMcccImpose()){
           if($ficheMatierePorteuse){
                foreach($structEc->mcccs as $mccc){
                    if($isEcPorteur || $isEcOnlyOne){
                        $mcccCopy = $this->mcccCopyRepo->find($mccc->getId());
                        $ficheMatiereCopy = $this->fmCopyRepo->find($ficheMatierePorteuse->getId());
                        $mcccCopy->setFicheMatiere($ficheMatiereCopy);

                        if(array_key_exists($ficheMatiereCopy->getId(), $this->mcccCopyDataArray) === false){
                            $this->mcccCopyDataArray[$ficheMatiereCopy->getId()] = [];
                        }
                        $this->mcccCopyDataArray[$ficheMatiereCopy->getId()][] = $mccc;

                        $this->entityManagerCopy->persist($mcccCopy);
                    }
                }
           }
        }
    }

    /**
     * Les données sont récupérées depuis la copie
     * Il n'y a plus besoin de sélectionner l'élément en BD
     */
    public function placeMcccSpecifiquesFlag(StructureEc $structEc){
        $isMcccParentIdentique = $structEc->elementConstitutif->getEcParent()?->isMcccEnfantsIdentique();
        $mcccResult = null;
        if($structEc->elementConstitutif->getFicheMatiere()){
            if(array_key_exists($structEc->elementConstitutif->getFicheMatiere()->getId(), $this->mcccCopyDataArray)){
                $mcccResult = $this->mcccCopyDataArray[$structEc->elementConstitutif->getFicheMatiere()->getId()];
            }
        }
        if($isMcccParentIdentique){
            $mcccResult = $structEc->elementConstitutif->getEcParent()->getMcccs()->toArray();
        }
        if($mcccResult){
            usort($mcccResult, fn($a, $b) => $a->getId() <=> $b->getId());
            usort($structEc->mcccs, fn($a, $b) => $a->getId() <=> $b->getId());

            if(count($mcccResult) === count($structEc->mcccs)){
                $mcccAreEqual = true;
                foreach($structEc->mcccs as $index => $value){
                    if($this->compareTwoMCCC($structEc->mcccs[$index], $mcccResult[$index]) === false){
                        $mcccAreEqual = false;
                    }
                }
            }else {
                $mcccAreEqual = false;
            }
            
            if($mcccAreEqual === false){
                $ecCopyMccc = $this->ecCopyRepo->find($structEc->elementConstitutif->getId());
                $ecCopyMccc->setMcccSpecifiques(true);
                $this->entityManagerCopy->persist($ecCopyMccc);
            }
        }
    }

    public function getDTOForParcours(
        Parcours $parcours, 
        bool $dataFromFicheMatiere = false, 
        bool $withCopy = false,
        bool $fromCopy = false
    ){
        if($parcours->getTypeDiplome()->getLibelleCourt() === 'BUT'){
            $calcul = new CalculButStructureParcours();
            $dto = $calcul->calcul($parcours);

            return $dto;
        }
        else {
            if($fromCopy || $withCopy){
                $ueCopyRepository = new UeCopyRepository($this->entityManagerCopy, Ue::class);
                $ecCopyRepository = new ElementConstitutifCopyRepository($this->entityManagerCopy, ElementConstitutif::class);
                $calcul = new CalculStructureParcours($this->entityManagerCopy, $ecCopyRepository, $ueCopyRepository);
            }else {
                $ueRepository = $this->entityManager->getRepository(Ue::class);
                $ecRepository = $this->entityManager->getRepository(ElementConstitutif::class);
                $calcul = new CalculStructureParcours($this->entityManager, $ecRepository, $ueRepository);
            }
            if($withCopy){
                $parcoursData = $parcours;
                $this->copyDataForParcours($parcoursData);
                $dto = $calcul->calcul($parcoursData, dataFromFicheMatiere: $dataFromFicheMatiere);
            }   
            else {
                $dto = $calcul->calcul($parcours, dataFromFicheMatiere: $dataFromFicheMatiere);
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
        bool $withCopy = false,
        bool $fromCopy = false,
    ){
        return $this->myPdf->render(
            'typeDiplome/formation/_structure.html.twig',
            [
                'print' => true,
                'dto' => $this->getDTOForParcours($parcours, $heuresSurFicheMatiere, $withCopy, $fromCopy),
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

    public function compareTwoDtoForMCCC(StructureParcours $dto1, StructureParcours $dto2){
        $result = true;
        foreach($dto1->semestres as $indexSemestre => $semestre){
            foreach($semestre->ues as $indexUe => $ue){
                $testUe = $this->compareTwoUeDtoForMccc(
                    $ue, $dto2->semestres[$indexSemestre]->ues[$indexUe], $dto1->parcours->getId()
                );
                $result = $result && $testUe;
                foreach($ue->uesEnfants() as $indexUeEnfant => $ueEnfant){
                    $testUeEnfant = $this->compareTwoUeDtoForMccc(
                        $ueEnfant, 
                        $dto2->semestres[$indexSemestre]->ues[$indexUe]->uesEnfants()[$indexUeEnfant],
                        $dto1->parcours->getId()
                    );
                    $result = $result && $testUeEnfant;
                    foreach($ueEnfant->uesEnfants() as $indexUeEnfantDeuxieme => $ueEnfantDeuxieme){
                        $testUeEnfantDeuxieme = $this->compareTwoUeDtoForMccc(
                            $ueEnfantDeuxieme,
                            $dto2->semestres[$indexSemestre]->ues[$indexUe]
                                ->uesEnfants()[$indexUeEnfant]
                                ->uesEnfants()[$indexUeEnfantDeuxieme],
                            $dto1->parcours->getId()
                        );
                        $result = $result && $testUeEnfantDeuxieme;
                    }
                }
            }
        }

        return $result;
    }

    public function compareTwoUeDtoForMccc(StructureUe $ue1, StructureUe $ue2, int $parcoursId){
        $result = true;
        if(count($ue1->elementConstitutifs) !== count($ue2->elementConstitutifs)){
            $result = false;
        }
        if($result){
            foreach($ue1->elementConstitutifs as $indexEc => $ec){
                foreach($ec->mcccs as $indexMccc => $mccc){
                    $mcccTest = $this->compareTwoMCCC(
                        $mccc,
                        $ue2->elementConstitutifs[$indexEc]?->mcccs[$indexMccc] ?? null,
                        $parcoursId,
                        $ue1->display . " " . $ec->elementConstitutif->getCode()
                    );
                    $result = $result && $mcccTest;
                }
                foreach($ec->elementsConstitutifsEnfants as $indexEcEnfant => $ecEnfant){
                    foreach($ecEnfant->mcccs as $indexMcccEnfant => $mcccEnfant){
                        $mcccEnfantTest = $this->compareTwoMCCC(
                            $mcccEnfant,
                            $ue2->elementConstitutifs[$indexEc]?->elementsConstitutifsEnfants[$indexEcEnfant]?->mcccs[$indexMcccEnfant] ?? null,
                            $parcoursId,
                            $ue1->display . " " . $ec->elementConstitutif->getCode()
                        );
                        $result = $result && $mcccEnfantTest;
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

    public function hasEcSameHeuresAsFicheMatiereCopy(
        ElementConstitutif $ec, 
        FicheMatiere $ficheMatiere
    ){  
        if(array_key_exists($ficheMatiere->getId(), $this->ficheMatiereCopyDataArray)){
            $ficheMatiereCopy = $this->ficheMatiereCopyDataArray[$ficheMatiere->getId()];

            $fmCmPres = $ficheMatiereCopy["cmPres"] ?? 0.0;
            $fmCmDist = $ficheMatiereCopy["cmDist"] ?? 0.0;
            $fmTdPres = $ficheMatiereCopy["tdPres"] ?? 0.0;
            $fmTdDist = $ficheMatiereCopy["tdDist"] ?? 0.0;
            $fmTpPres = $ficheMatiereCopy["tpPres"] ?? 0.0;
            $fmTpDist = $ficheMatiereCopy["tpDist"] ?? 0.0;
            $fmTe = $ficheMatiereCopy["te"] ?? 0.0;

            $ecTe = $ec->getVolumeTe() ?? 0.0;
    
            return $fmCmPres === $ec->getVolumeCmPresentiel() 
            && $fmCmDist === $ec->getVolumeCmDistanciel() 
            && $fmTdPres === $ec->getVolumeTdPresentiel() 
            && $fmTdDist === $ec->getVolumeTdDistanciel() 
            && $fmTpPres === $ec->getVolumeTpPresentiel() 
            && $fmTpDist === $ec->getVolumeTpDistanciel() 
            && $fmTe === $ecTe;
        }

        return null;
                         
    }

    public function hasHeuresFicheMatiereCopy(FicheMatiere $ficheMatiere) : bool {
        $haystack = [0, null];

        if(array_key_exists($ficheMatiere->getId(), $this->ficheMatiereCopyDataArray)){
            // $ficheMatiereCopy = $this->ficheMatiereCopyDataArray[$ficheMatiere->getId()];
            // return in_array($ficheMatiereCopy["cmPres"], $haystack) === false
            //     || in_array($ficheMatiereCopy["tdPres"], $haystack) === false
            //     || in_array($ficheMatiereCopy["tpPres"], $haystack) === false
            //     || in_array($ficheMatiereCopy["cmDist"], $haystack) === false
            //     || in_array($ficheMatiereCopy["tdDist"], $haystack) === false
            //     || in_array($ficheMatiereCopy["tpDist"], $haystack) === false
            //     || in_array($ficheMatiereCopy["te"], $haystack) === false;

            // La FM est dans le tableau, donc elle a été traitée
            return true;
        }

        return false;
    }

    public function hasEcHeures(ElementConstitutif $ec){
        if($ec->isSansHeure()){
            return false;
        }

        $haystack = [0, null];
        return in_array($ec->getVolumeCmPresentiel(), $haystack) === false
        || in_array($ec->getVolumeCmDistanciel(), $haystack) === false
        || in_array($ec->getVolumeTdPresentiel(), $haystack) === false
        || in_array($ec->getVolumeTdDistanciel(), $haystack) === false
        || in_array($ec->getVolumeTpPresentiel(), $haystack) === false
        || in_array($ec->getVolumeTpDistanciel(), $haystack) === false
        || in_array($ec->getVolumeTe(), $haystack) === false;
    }

    public function compareTwoMCCC(
        ?Mccc $mccc1,
        ?Mccc $mccc2,
        int $parcoursId = -1,
        string $debugText = "",
        bool $withDebug = true
    ) : bool{
        $retour = true;
        $variableError = [];

        if( ($mccc1 === null && $mccc2 !== null) || ($mccc1 !== null && $mccc2 === null) ){
            $retour = false;
            $variableError[] = "MCCC 'null' alors que son équivalent à une valeur";
        }

        if($mccc1 !== null && $mccc2 !== null){
            if($mccc1->getLibelle() !== $mccc2->getLibelle()){
                $retour = false;
                $variableError[] = "Libellé";
            }
            if($mccc1->getNumeroSession() !== $mccc2->getNumeroSession()){
                $retour = false;
                $variableError[] = "Numéro de sesssion";
            }
            if($mccc1->isSecondeChance() !== $mccc2->isSecondeChance()){
                $retour = false;
                $variableError[] = "Seconde chance";
            }
            if($mccc1->getPourcentage() !== $mccc2->getPourcentage()){
                $retour = false;
                $variableError[] = "Pourcentage";
            }
            if($mccc1->getNbEpreuves() !== $mccc2->getNbEpreuves()){
                $retour = false;
                $variableError[] = "Nb. épreuves";
            }
            // Comparaison du tableau de type épreuve
            if($this->twoArrayAreIdentical($mccc1->getTypeEpreuve(), $mccc2->getTypeEpreuve()) === false){
                $retour = false;
                $variableError[] = "Type épreuve";
            }
            if($mccc1->isControleContinu() !== $mccc2->isControleContinu()){
                $retour = false;
                $variableError[] = "Contrôle continu";
            }
            if($mccc1->isExamenTerminal() !== $mccc2->isExamenTerminal()){
                $retour = false;
                $variableError[] = "Examen terminal";
            }
            // Comparaison de date
            $dateFormat = "d-m-Y H:i:s";
            if($mccc1->getDuree()?->format($dateFormat) !== $mccc2->getDuree()?->format($dateFormat)){
                $retour = false;
                $variableError[] = "Durée";
            }
            if($mccc1->getNumeroEpreuve() !== $mccc2->getNumeroEpreuve()){
                $retour = false;
                $variableError[] = "Numéro épreuve";
            }
            // Si une option est nulle mais que son équivalent ne l'est pas
            if(
                ($mccc1->getOptions() !== null && $mccc2->getOptions() === null )
                || 
                ($mccc1->getOptions() === null && $mccc2->getOptions() !== null)
            ){
                $retour = false;
                $variableError[] = "Option à 'null' alors que son équivalent ne l'est pas";
            }
            // Comparaison du tableau d'options
            if($mccc1->getOptions() && $mccc2->getOptions()){
                if($this->twoArrayAreIdentical($mccc1->getOptions(), $mccc2->getOptions()) === false){
                    $retour = false;
                    $variableError[] = "Options";
                }
            }
        }
        if($retour === false){
            if($withDebug){
                $dataError = $debugText . " : Les deux MCCC ({$mccc1->getId()}) ne correspondent pas : [" . implode(", ", $variableError) . "]";
                if(array_key_exists($parcoursId, self::$errorMcccMessageArray) === false){
                    self::$errorMcccMessageArray[$parcoursId] = [];
                }
                self::$errorMcccMessageArray[$parcoursId][] = $dataError;
            }
        }

        return $retour;
    }

    private function twoArrayAreIdentical(array $array1, array $array2){
        $retour = true;
        if(count($array1) !== count($array2)){
            $retour = false;
        }
        foreach($array1 as $key => $value){
            if(array_key_exists($key, $array2)){
                if($value !== $array2[$key]){
                    $retour = false;
                }
            }
            else {
                $retour = false;
            }
        }
        return $retour;
    }
}