<?php

namespace App\Command;

use App\Classes\CalculButStructureParcours;
use App\Classes\CalculStructureParcours;
use App\Classes\GetElementConstitutif;
use App\DTO\StructureEc;
use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\Entity\ElementConstitutif;
use App\Entity\Formation;
use App\Entity\HistoriqueFormation;
use App\Entity\Parcours;
use App\Entity\Semestre;
use App\Entity\Ue;
use App\Enums\Apogee\CodeNatuElpEnum;
use App\Enums\Apogee\TypeHeureCE;
use App\Enums\TypeUeEcEnum;
use App\Repository\ElementConstitutifRepository;
use App\Repository\UeRepository;
use App\Service\Apogee\Classes\ElementPedagogiDTO6;
use App\Service\Apogee\Classes\ListeElementPedagogiDTO3;
use App\Service\Apogee\Classes\ParametrageAnnuelCeDTO2;
use App\Service\Apogee\Classes\TableauParametrageChargeEnseignementDTO2;
use App\Service\Apogee\Classes\TableauTypeHeureDTO;
use App\Service\Apogee\Classes\TypeHeureDTO;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use stdClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:export-elp-apogee',
    description: "Exporte les éléments pédagogiques (ELP) dans l'application APOGEE",
)]
class ExportElpApogeeCommand extends Command
{

    private EntityManagerInterface $entityManager;
    private ElementConstitutifRepository $elementConstitutifRepository;
    private UeRepository $ueRepository;
    private Filesystem $filesystem;
    private ParameterBagInterface $parameterBag;
    private ?\SoapClient $soapClient;

    public static array $errorMessagesArray = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        ElementConstitutifRepository $elementConstitutifRepository,
        UeRepository $ueRepository,
        Filesystem $filesystem,
        ParameterBagInterface $parameterBag
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->elementConstitutifRepository = $elementConstitutifRepository;
        $this->ueRepository = $ueRepository;
        $this->filesystem = $filesystem;
        $this->parameterBag = $parameterBag;
        $this->soapClient = null;
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'mode',
            mode: InputOption::VALUE_OPTIONAL,
            description: "Mode d'exécution : 'test' ou 'production'", 
            default: 'test'
        )->addOption(
            name: 'full-excel-export', 
            mode: InputOption::VALUE_REQUIRED,
            description: 'Génère un export Excel des ELP pour toutes les formations - Type : Semestre, UE, EC'
        )->addOption(
            name: 'dummy-insertion',
            mode: InputOption::VALUE_NONE,
            description: "Insère un ELP dans la base de données APOTEST"
        )->addOption(
            name: 'dummy-lse-insertion',
            mode: InputOption::VALUE_NONE,
            description: "Insère une LSE dans la base de données APOTEST"
        )->addOption(
            name: 'parcours-insertion',
            mode: InputOption::VALUE_REQUIRED,
            description: "Insère tous les ELP d'un parcours dans la base de données, via le Web Service"
        )
        ->addOption(
            name: 'full-parcours-insertion',
            mode: InputOption::VALUE_NONE,
            description: "Insère tous les ELP de tous les parcours disponibles en base de données"
        )->addOption(
            name: 'full-lse-insertion',
            mode: InputOption::VALUE_NONE,
            description: "Insère toutes les listes LSE de tous les parcours disponibles"
        )
        ->addOption(
            name: 'parcours-excel-export',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Genère une export de tous les ELP pour un parcours donné.'
        )->addOption(
            name: 'check-duplicates',
            mode: InputOption::VALUE_NONE,
            description: "Vérifie s'il y a des doublons sur les codes Apogee depuis la base de données"
        )->addOption(
            name: 'full-verify-data',
            mode: InputOption::VALUE_NONE,
            description: "Instancie tous les ELP des parcours disponibles, et génère un compte-rendu selon les erreurs détectées."
        )->addOption(
            name: 'parcours-lse-excel-export',
            mode: InputOption::VALUE_REQUIRED,
            description: "Génère un export Excel pour les LSE d'un parcours"
        )->addOption(
            name: 'full-lse-excel-export',
            mode: InputOption::VALUE_NONE,
            description: "Génère l'export Excel pour les LSE de tous les parcours disponibles"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $mode = $input->getOption('mode');
        $fullExport = $input->getOption('full-excel-export');
        $parcoursExport = $input->getOption('parcours-excel-export');
        $dummyInsertion = $input->getOption('dummy-insertion');
        $dummyLseInsertion = $input->getOption('dummy-lse-insertion');
        $parcoursInsertion = $input->getOption('parcours-insertion');
        $fullParcoursInsertion = $input->getOption('full-parcours-insertion');
        $checkDuplicates = $input->getOption('check-duplicates');
        $fullVerifyData = $input->getOption('full-verify-data');
        $parcoursLseExport = $input->getOption('parcours-lse-excel-export');
        $fullLseExport = $input->getOption('full-lse-excel-export');
        $fullLseInsertion = $input->getOption('full-lse-insertion');

        if($mode === "test"){
            // Export total des ELP selon le type : EC, UE ou Semestre
            if($fullExport){
                switch(strtoupper($fullExport)){
                    case "EC":
                        $io->writeln("Génération de l'export Excel - EC...");
                        $this->saveFullExportAsSpreadsheet($output, "EC");
                        break;
                    case "UE":
                        $io->writeln("Génération de l'export Excel - UE...");
                        $this->saveFullExportAsSpreadsheet($output, "UE");
                        break;
                    case "SEMESTRE":
                        $io->writeln("Génération de l'export Excel - Semestres...");
                        $this->saveFullExportAsSpreadsheet($output, "SEMESTRE");
                        break;
                    case "PARCOURS":
                        $io->writeln("Génération de l'export Excel - Parcours...");
                        $this->saveFullExportAsSpreadsheet($output, "PARCOURS");
                        break;
                    default: 
                        $io->warning("Type d'export inconnu. Il devrait être parmi la liste : ['PARCOURS', 'SEMESTRE', 'UE', 'EC']");
                        return Command::INVALID;
                }
                $io->success("Fichier généré avec succès.");

                return Command::SUCCESS;
            }  
            // Export Excel d'un parcours suivant l'ID
            if($parcoursExport){
                $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById($parcoursExport);
                if($parcours){
                    $io->writeln('Parcours trouvé : ' . $parcours->getDisplay() . ' - Formation : ' . $parcours->getFormation()->getDisplayLong());
                    $io->writeln("Génération de l'export Excel...");
                    $soapObjectArray = $this->generateSoapObjectsForParcours($parcours);
                    $this->generateSpreadsheet($soapObjectArray, "Parcours-{$parcours->getId()}");
                    $io->success("Parcours enregistré avec succès.");
                    return Command::SUCCESS;
                }else {
                    $io->warning("Aucun parcours trouvé. L'identifiant est incorrect. ({$parcoursExport})");
                    return Command::INVALID;
                }
            }
            // Insertion d'un parcours suivant l'ID 
            if($parcoursInsertion){
                $io->write("Utilisation du Web Service APOTEST");
                if($this->verifyUserIntent($io, "Voulez-vous vraiment insérer un parcours dans la base de données ?")){
                    $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById($parcoursInsertion);
                    if($parcours){
                        $io->writeln('Parcours trouvé : ' . $parcours->getDisplay() . ' - Formation : ' . $parcours->getFormation()->getDisplayLong());
                        $soapObjectArray = $this->generateSoapObjectsForParcours($parcours);
                        $countElement = count($soapObjectArray);
                        if($this->verifyUserIntent($io, "Le parcours comprend {$countElement} ELP. Voulez-vous continuer ?")){
                            try{
                                $io->writeln("Appel au Web Service en cours...");
                                $this->createSoapClient();
                                $this->insertSeveralElp($soapObjectArray);
                                $io->success("Commande exécutée avec succès");
                                return Command::SUCCESS;
                            }catch(\Exception $e){
                                $io->writeln("Une erreur est survenue durant l'insertion.");
                                $io->writeln("Message : " . $e->getMessage());
                                return Command::FAILURE;
                            }
                        }else {
                            $io->warning("L'insertion des ELP a été annulée.");
                            return Command::SUCCESS;
                        }
                    }else {
                        $io->warning("Aucun parcours trouvé pour cet identifiant. ({$parcoursInsertion})");
                        return Command::INVALID;
                    }
                }
                else {
                    $io->warning("La commande d'insertion a été annulée.");
                    return Command::SUCCESS;
                }
            }
            // Insère tous les ELP de tous les parcours disponibles
            if($fullParcoursInsertion){
                $io->writeln("Utilisation du Web Service APOTEST");
                if($this->verifyUserIntent($io, "Voulez-vous vraiment insérer les ELP de TOUS LES PARCOURS ?")){
                    $parcoursArray = $this->retrieveParcoursDataFromDatabase();
                    $nbParcours = count($parcoursArray);
                    if($this->verifyUserIntent($io, "Il y a {$nbParcours} parcours disponibles. Continuer ?")){
                        try{
                            $io->writeln('Initialisation du Web Service...');
                            // $this->createSoapClient();
                            $io->writeln('Insertion des données en cours...');
                            $io->progressStart($nbParcours);
                            foreach($parcoursArray as $parcours){
                                $soapObjectArray = $this->generateSoapObjectsForParcours($parcours);
                                // $this->insertSeveralElp($soapObjectArray);
                                $io->progressAdvance();
                            }
                            $io->writeln("\nInsertion réussie !");
                            return Command::SUCCESS;
                        }catch(\Exception $e){
                            $io->writeln("Une erreur est survenue durant l'insertion.");
                            $io->writeln("Message : " . $e->getMessage());
                            return Command::FAILURE;
                        }
                    }
                    else {
                        $io->warning("La commande d'insertion a été annulée.");
                        return Command::SUCCESS;
                    }
                }
                else {
                    $io->warning("La commande d'insertion a été annulée.");
                    return Command::SUCCESS;
                }
            }
            // Instancie les ELP et génère un rapport des erreurs détectées
            if($fullVerifyData){
                // date et heure pour le fichier
                $now = new DateTime();
                $date = $now->format('d-m-Y_H-i-s');
                $io->writeln("Vérification des donnéees pour les parcours disponibles...");
                $parcoursArray = $this->retrieveParcoursDataFromDatabase();
                $io->progressStart(count($parcoursArray));
                foreach($parcoursArray as $parcours){
                    self::$errorMessagesArray[$parcours->getId()] = [];
                    $soapObjects = $this->generateSoapObjectsForParcours($parcours, true);
                    $io->progressAdvance();
                }
                $file = __DIR__ . "/../Service/Apogee/export/Parcours-invalides-{$date}.txt";
                $this->filesystem->appendToFile($file, "Compte Rendu des parcours invalides pour l'export APOGEE\n");
                $io->writeln("");
                $io->writeln("Traitement des erreurs détectées...");
                $hasError = false;
                foreach(self::$errorMessagesArray as $id => $errorArray){
                    if(count($errorArray) > 0){
                        $hasError = true;
                        $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById($id);
                        $this->filesystem->appendToFile($file, "\nParcours {$id} - {$parcours->getDisplay()} :\n");
                        foreach($errorArray as $error){
                            $this->filesystem->appendToFile($file, "{$error}\n");
                        }
                    }
                }
                $io->writeln("Rapport d'erreurs généré avec succès.");
                $hasError ? $io->warning("Un ou plusieurs parcours est incorrect.") : $io->success("Aucun problème détecté.");
                return Command::SUCCESS;
            }
            // Vérification des doublons sur les codes Apogee
            if($checkDuplicates){
                $io->writeln("Vérification de la présence de doublons sur les codes Apogee depuis la base de données...");
                // nombre de doubles
                $nbEcDuplicates = count($this->entityManager->getRepository(ElementConstitutif::class)->countDuplicatesCode());
                $nbUeDuplicates = count($this->entityManager->getRepository(Ue::class)->countDuplicatesCode());
                $nbSemestreDuplicates = count($this->entityManager->getRepository(Semestre::class)->countDuplicatesCode());
                // affichage des résultats
                $io->writeln("Nombre de doublons sur les EC : " . $nbEcDuplicates);
                $io->writeln("Nombre de doublons sur les UE : " . $nbUeDuplicates);
                $io->writeln("Nombre de doublons sur les Semestres : " . $nbSemestreDuplicates);
                return Command::SUCCESS;
            }
            // Export Excel des LSE d'un parcours
            if($parcoursLseExport){ 
                $io->writeln("Génération d'export Excel des LSE d'un parcours.");
                $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById($parcoursLseExport);
                if($parcours){
                    $io->writeln("Parcours trouvé - Formation : " . $parcours->getDisplay());
                    $dto = $this->getDTOForParcours($parcours);
                    $lseArray = $this->getLseObjectArrayForParcours($dto);
                    $this->generateSpreadsheetForLSE($lseArray, 'Parcours_' . $parcours->getId());
                    $io->success("Export des LSE généré avec succès.");
                    return Command::SUCCESS;
                }else {
                    $io->warning("Identifiant du Parcours incorrect. (" . $parcoursLseExport . ")");
                    return Command::FAILURE;
                }
            }
            // Export Excel des LSE de tous les parcours disponibles
            if($fullLseExport){
                $io->writeln("Génération des LSE pour tous les parcours...");
                $parcoursArray = $this->retrieveParcoursDataFromDatabase();
                $io->progressStart(count($parcoursArray));
                $lseArray = [];
                foreach($parcoursArray as $parcours){
                    $lseArray[] = $this->getLseObjectArrayForParcours($this->getDTOForParcours($parcours));
                    $io->progressAdvance();
                }
                $lseArray = array_merge(...$lseArray);
                $this->generateSpreadsheetForLSE($lseArray, "ALL");
                $io->writeln("\nCréation du fichier Excel...");
                $io->success("Export de tous les LSE généré avec succès.");
                return Command::SUCCESS;
            }
            if($fullLseInsertion){
                $io->writeln("Utilisation du Web Service APOTEST");
                if($this->verifyUserIntent($io, "Voulez-vous vraiment insérer les LSE de TOUS LES PARCOURS ?")){
                    $parcoursArray = $this->retrieveParcoursDataFromDatabase();
                    $nbParcours = count($parcoursArray);
                    if($this->verifyUserIntent($io, "Il y a {$nbParcours} parcours à traiter. Continuer ?")){
                        $io->writeln("Initialisation du Web Service...");
                        // $this->createSoapClient();
                        $io->progressStart($nbParcours);
                        foreach($parcoursArray as $parcours){
                            $dto = $this->getDTOForParcours($parcours);
                            $lseArray = $this->getLseObjectArrayForParcours($dto);
                            // $this->insertSeveralLSE($lseArray);
                            $io->progressAdvance();
                        }
                        $io->writeln("\nInsertion réussie !");
                        return Command::SUCCESS;
                    }
                    else {
                        $io->warning("La commande d'insertion a été annulée.");
                        return Command::SUCCESS;
                    }
                }
                else {
                    $io->warning("La commande d'insertion a été annulée.");
                    return Command::SUCCESS;
                }
            }
            // Insertion de test d'un ELP
            if($dummyInsertion){
                $io->write("Utilisation du Web Service APOTEST");
                if($this->verifyUserIntent($io, "Voulez-vous vraiment insérer dans APOTEST (ELP) ?")){
                    // Récupération des donnéees à insérer
                    $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById(405);
                    $dto = $this->getDTOForParcours($parcours);
                    $ec = $dto->semestres[1]->ues()[0]->elementConstitutifs[0];
                    // $elp = new ElementPedagogiDTO6($ec, $dto);
                    $elp = $this->setObjectForSoapCall($ec, $dto, CodeNatuElpEnum::MATI, false);
                    $elp->codElp = 'TEST111';
                    $elp->codNatureElp = 'MATI';
                    $elp->libCourtElp = "TEST WS PHP 2";
                    $elp->libElp = "TEST WEBSERVICE PHP 23022024";
                    dump($elp);
                    if($this->verifyUserIntent($io, "Les données affichées (ELP) conviennent-elles ?")){
                        try{
                            $io->writeln("Initialisation du Web Service...");
                            // Création du client SOAP
                            $this->createSoapClient();
                            $io->writeln('Création du client SOAP réussie.');
                            // Insertion d'un élément
                            $result = $this->insertOneElp($elp);
                            $io->writeln("Résultat de l'appel au Web Service :");
                            dump($result);
                            $io->writeln('Insertion réussie !');
                            return Command::SUCCESS;
                        }catch(\Exception $e){
                            $io->writeln("Une erreur est survenue durant l'insertion. (ELP)");
                            $io->writeln("Message : " . $e->getMessage());
                            return Command::FAILURE;
                        }

                    }else {
                        $io->warning('La commande a été annulée.');
                        return Command::SUCCESS;    
                    }
                }
                else {
                    $io->warning('La commande a été annulée.');
                    return Command::SUCCESS;
                }
            
                return Command::SUCCESS;
            }
            // Insertion de test d'une liste (LSE)
            if($dummyLseInsertion){
                if($this->verifyUserIntent($io, "Voulez-vous vraiment insérer dans APOTEST (LSE) ?")){
                    // Création des données à insérer
                    $lseObject = new ListeElementPedagogiDTO3(
                        'TEST110', 'O', 'TEST INSERT LISTE WS',
                        'TEST INSERTION LISTE WEB SERVICE', 
                        ['TEST110', 'TEST111']
                    );
                    dump($lseObject);
                    if($this->verifyUserIntent($io, "Les données à insérer (LSE) conviennent-elles ?")){
                        try{
                            // Création du client SOAP
                            $io->writeln("Initialisation du Web Service...");
                            $this->createSoapClient();
                            $io->writeln("Création du client SOAP réussie.");
                            // Insertion de la liste LSE
                            $result = $this->insertOneLSE($lseObject);
                            $io->writeln("Résultat de l'appel au Web Service :");
                            dump($result);
                            $io->writeln('Insertion réussie !');
                            return Command::SUCCESS;
                        }catch(\Exception $e){
                            $io->writeln("Une erreur est survenue durant l'insertion. (LSE)");
                            $io->writeln("Message : " . $e->getMessage());
                            return Command::FAILURE;
                        }
                    }
                    else {
                        $io->warning('La commande a été annulée.');
                        return Command::SUCCESS;    
                    }
                }
                else {
                    $io->warning('La commande a été annulée.');
                    return Command::SUCCESS;
                }

            }     
            
            return Command::SUCCESS;
        }

        elseif($mode === "production"){
            $io->warning("Commande en mode PRODUCTION - O.K");
            return Command::INVALID;
        }
        else{
            $io->error("Le mode d'exécution est invalide. Il devrait être 'test' ou 'production'");
            return Command::FAILURE;
        }
    }

    /**
     * Créer un ELP à partir d'un Semestre, UE, ou EC
     * @param StructureEC|StructureUe|StructureSemestre $elementPedagogique Données sources
     * @param StructureParcours $dto DTO du parcours
     * @param ?CodeNatuElmEnum $natureElp Nature de l'élément pédagogique
     * @param bool $withChecks Si des messages d'erreurs doivent être générés
     */
    private function setObjectForSoapCall(
        StructureEc|StructureUe|StructureSemestre $elementPedagogique, 
        StructureParcours $dto,
        ?CodeNatuElpEnum $natureElp = null,
        bool $withChecks = false
    ) : ElementPedagogiDTO6 {
        $tableauParamCE = [];
        if(in_array($natureElp->value, ['MATI', 'MATM', 'MATP', 'MATS']) && $elementPedagogique instanceof StructureEc){
           $tableauParamCE = $this->configureChargeEnseignementForEC($elementPedagogique);
        }
        return new ElementPedagogiDTO6($elementPedagogique, $dto, $natureElp, $withChecks, $tableauParamCE);
    }

    /**
     * Exporte tous les ELP d'une formation, en sélectionnant les EC, UE, ou Semestres
     * @param OutputInterface $output Sortie de la commande
     * @param string $type Sélectionne le type, dans la liste : [EC, UE, SEMESTRE]
     */
    private function saveFullExportAsSpreadsheet(OutputInterface $output, string $type){
        // retrieve data
        $dataArray = $this->retrieveParcoursDataFromDatabase();
        $totalElement = count($dataArray);
        // progress bar
        $progressBar = new ProgressBar($output, $totalElement);
        // transform into valid soap object
        $soapObjectArray = [];
        // export typename for file
        $exportTypeName = "";
        foreach($dataArray as $parcours){
            $dto = $this->getDTOForParcours($parcours);
            if($type === "EC"){
                foreach($dto->semestres as $semestre) {
                    // UE 
                    foreach($semestre->ues() as $ue) {
                        // EC à insérer
                        foreach($ue->elementConstitutifs as $ec) {
                            $this->addEcToElpArray($soapObjectArray, $ec, $dto);
                        }
                        // UE Enfants
                        foreach($ue->uesEnfants() as $ueEnfant) {
                            foreach($ueEnfant->elementConstitutifs as $ec) {
                                // EC à insérer
                                $this->addEcToElpArray($soapObjectArray, $ec, $dto);
                            }
                        }
                    }
                }
                $exportTypeName = "EC";
                $progressBar->advance();
            }
            elseif ($type === "UE") {
                foreach($dto->semestres as $semestre){
                    foreach($semestre->ues() as $ue){
                        $this->addUeToElpArray($soapObjectArray, $ue, $dto);
                    }
                }
                $exportTypeName = "UE";
                $progressBar->advance();
            }
            elseif ($type === "SEMESTRE"){
                foreach($dto->semestres as $semestre){
                    $this->addSemestreToElpArray($soapObjectArray, $semestre, $dto);
                }
                $exportTypeName = "SEMESTRE";
                $progressBar->advance();
            }
            elseif ($type === "PARCOURS"){
                $exportTypeName = "ALL_PARCOURS";
                $soapObjectArray[] = $this->generateSoapObjectsForParcours($parcours, false);
                $progressBar->advance();
            }
            
        }
        if($type === "PARCOURS"){
            $soapObjectArray = array_merge(...$soapObjectArray);
        }
        $this->generateSpreadsheet($soapObjectArray, $exportTypeName);
    }

    /**
     * Crée les ELP correspondants depuis un parcours passé en paramètre
     * @param Parcours $parcours Le parcours que l'on souhaite générer en ELP
     * @return array Tableau comprenant tous les ELP d'un parcours
     */
    private function generateSoapObjectsForParcours(Parcours $parcours, bool $withChecks = false) : array {
        $dto = $this->getDTOForParcours($parcours);
        $soapObjectArray = [];
        foreach($dto->semestres as $semestre){
            if($semestre->semestre->isNonDispense() === false){
                $this->addSemestreToElpArray($soapObjectArray, $semestre, $dto, $withChecks);
                foreach($semestre->ues() as $ue){
                    $this->addUeToElpArray($soapObjectArray, $ue, $dto, $withChecks);
                    foreach($ue->elementConstitutifs as $ec){
                        $this->addEcToElpArray($soapObjectArray, $ec, $dto, $withChecks);
                    }
                }
            }
        }
        
        return $soapObjectArray;
    }

    /**
     * Permet d'obtenir la structure DTO pour un parcours donné
     * @param Parcours $parcours
     * @return StructureParcours DTO du parcours
     */
    private function getDTOForParcours(Parcours $parcours){
        if($parcours->getFormation()->getTypeDiplome()->getLibelleCourt() === "BUT"){
            $calculStructure = new CalculButStructureParcours(); 
        }
        else {
            $calculStructure = new CalculStructureParcours($this->entityManager, $this->elementConstitutifRepository, $this->ueRepository);
        }
        return $calculStructure->calcul($parcours);
    }

    /**
     * Génère dans un fichier Excel les ELP fournis en paramètre.
     * @param array $ElpArray Les éléments pédagogiques : ElementPedagogiDTO6[]
     * @return void
     */
    private function generateSpreadsheet(array $ElpArray, string $exportTypeName = ""){
        // spreadsheet headers
        $headers = [
            "codElp", "libCourtElp", "libElp", "codNatureElp",
            "codComposante", "temModaliteControle", "nbrCredits",
            "volume", "uniteVolume", "codPeriode", "listeCentreInsPedagogi",
            "paramCE"
        ];
        // cast element into array values
        $ElpArray = array_map(
            fn($elp) => [
                $elp->codElp, $elp->libCourtElp, $elp->libElp, 
                $elp->codNatureElp, $elp->codComposante, $elp->temModaliteControle, 
                $elp->nbrCredits, $elp->volume, $elp->uniteVolume, $elp->codPeriode,
                // CIP list
                implode(", ", array_map(
                        fn($cip) => $cip->codCentreInsPedagogi,
                        $elp->listCentreInsPedagogi->centreInsPedagogi
                    )
                ), isset($elp->listParamChargEns) ? $elp->listParamChargEns->printInformation() : ""
            ], 
            $ElpArray
        );
        // Write to spreadsheet
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->fromArray($headers);
        $activeWorksheet->fromArray($ElpArray, startCell: "A2");
        // Write to file
        $now = new DateTime();
        $date = $now->format('d-m-Y_H-i-s');
        $filename = __DIR__ . "/../Service/Apogee/export/{$exportTypeName}-ELP-export-{$date}.xlsx";
        $this->filesystem->dumpFile($filename, "");
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($filename);
    }

    /**
     * Prompt pour poser une question à l'utilisateur de la commande
     * @param SymfonyStyle $io Entrée / Sortie de la commande
     * @param string $message Question à poser 
     * @return boolean Vrai si l'utilisateur confirme la question, Faux sinon
     */
    private function verifyUserIntent(SymfonyStyle $io, string $message) : bool {
        return $io->ask("{$message} [Y/n]", 'n', function($message) use ($io) {
            if($message === "Y"){
                return true;
            }
            else {
                return false;
            }
        });
    }

    /**
     * Création du client SOAP, selon la WSDL fournie dans .env.local
     */
    private function createSoapClient() : void {
        $wsdl = $this->parameterBag->get('WSDL_APOTEST');
        $this->soapClient = new \SoapClient($wsdl, [
            "trace" => true
        ]);
    }

    /**
     * Méthode pour appeler la fonction d'insertion d'un ELP du Web Service Apogee
     * @param ElementPedagogiDTO6 $elementPedagogique Élément à insérer
     */
    private function insertOneElp(ElementPedagogiDTO6 $elementPedagogique) {
        $param = new stdClass();
        $param->elementPedagogi = $elementPedagogique;
        if($this->soapClient){
            return $this->soapClient->__soapCall("creerModifierELP", [$param]);
        }
        else {
            throw new \Exception("Soap Client is not initialized.");
        }
    }

    /**
     * Appelle la fonction d'insertion d'une liste LSE du Web Service Apogee
     * @param ListeElementPedagogiDTO3 $lseObject Élément à insérer via le WS
     */
    private function insertOneLSE(ListeElementPedagogiDTO3 $lseObject){
        $param = new stdClass();
        $param->listeElementPedagogi = $lseObject;
        if($this->soapClient){
            return $this->soapClient->__soapCall("creerLSE", [$param]);
        }
        else {
            throw new \Exception("Soap Client is not initialized.");
        }
    }

    /**
     * Appelle le Web Service d'insertion d'ELP d'Apogee, 
     * et insère plusieurs valeurs d'un coup
     * @param array $elpArray
     */
    private function insertSeveralElp(array $elpArray){
        $dataWS = array_map([$this, 'mapDataForWebService'], $elpArray);
        if($this->soapClient){
            foreach($dataWS as $elpWS){
                $this->soapClient->__soapCall("creerModifierELP", [$elpWS]);
            }
        }else {
            throw new \Exception('Soap Client is not initialized.');
        }
    }

    /**
     * Appelle le Web Service d'insertion des listes LSE d'Apogee
     * avec plusieurs valeurs 
     * @param array $lseArray Tableau contenant les objets LSE
     */
    private function insertSeveralLSE(array $lseArray){
        $dataWS = array_map([$this, 'mapDataLseForWebService'], $lseArray);
        if($this->soapClient){
            foreach($dataWS as $lseWS){
                $this->soapClient->__soapCall('creerLSE', [$lseWS]);
            }
        }
        else {
            throw new \Exception('Soap Client is not initialized.');
        }
    }

    /**
     * Méthode pour filtrer les formations, si elles sont étiquettées comme "publication"
     * et si elles ne font pas partie des IUT
     * @param Formation $formation Formation que l'on souhaite tester
     * @return boolean Vrai si la formation peut être pris en compte, Faux si elle doit être écartée du jeu de données.
     */
    private function filterFormationByPublicationState(Formation $formation) : bool {
        $return = false;
        $historique = $this->entityManager->getRepository(HistoriqueFormation::class)->findBy(
            ['formation' => $formation],
            ['date' => 'DESC']
        );
        if(count($historique) > 0){
            // dernier état est 'publication'
            $return = $historique[0]->getEtape() === "publication"
            // exclusion des IUT
            && in_array($formation->getComposantePorteuse()?->getCodeComposante(), ['980', '983', '984', '985']) === false;
        }
        
        return $return;
    }

    /**
     * Permet de savoir si une matière mutualisée est la matière porteuse (maître)
     * @return boolean Vrai si la matière est porteuse, Faux sinon
     */
    private function isEcMutualiseMaster(StructureEc $ec, StructureParcours $dto) : bool {
        // si la matière est mutualisée et est l'élément maître
        return $this->isEcMutualise($ec)
        && $dto->parcours->getId() === $ec->elementConstitutif->getFicheMatiere()?->getParcours()?->getId();
    }

    /**
     * Permet de savoir si une matière est mutualisée avec un autre parcours
     * @return boolean Vrai si la matière est mutualisée, Faux sinon
     */
    private function isEcMutualise(StructureEc $ec) : bool {
        return count($ec->elementConstitutif->getFicheMatiere()?->getFicheMatiereParcours() ?? []) >= 1;
    }

    /**
     * Permet de savoir si une UE est mutualisée
     * @param StructureUe $ue UE à tester
     * @return boolean Vrai si elle est mutualisée, Faux sinon
     */
    private function isUeMutualise(StructureUe $ue) : bool {
        return count($ue->ue->getUeMutualisables()) >= 2;
    }

    /**
     * Teste si une UE qui est mutualisée est l'UE porteuse (maître)
     * @param StructureUe $ue UE à tester
     * @return boolean Vrai si l'UE est celle porteuse, Faux sinon
     */
    private function isUeMutualiseMaster(StructureUe $ue) : bool {
        // TO DO
        return true;
    }

    /**
     * Ajoute une matière (élément constitutif) dans le tableau d'ELP,
     * avec les types adéquats
     * @param array &$elpArray Tableau dans lequel on insère l'élément
     * @param StructureEc $ec Element Constitutif
     * @param StructureParcours $dto Structure DTO du parcours complet
     * @param bool $withChecks Si des messages d'erreurs doivent être générés
     * @return void
     */
    private function addEcToElpArray(array &$elpArray, StructureEc $ec, StructureParcours $dto, bool $withChecks = false) : void {
        $hasChildren = count($ec->elementsConstitutifsEnfants) > 0;
        // si l'élément est mutualisé, on ne l'insère qu'une fois
        if($this->isEcMutualiseMaster($ec, $dto) && $hasChildren === false){
            $nature = CodeNatuElpEnum::MATM;
            if($ec->elementConstitutif->getTypeEc()->getType() === TypeUeEcEnum::STAGE){
                $nature = CodeNatuElpEnum::MATS;
            }
            $elpArray[] = $this->setObjectForSoapCall($ec, $dto, $nature, $withChecks);
        }
        // si l'élément a des enfants, on insère que les enfants
        if($hasChildren){
            // Ne pas insérer les éléments de nature 'CHOI'
            // $elpArray[] = $this->setObjectForSoapCall($ec, $dto, CodeNatuElpEnum::CHOI, $withChecks);
            foreach($ec->elementsConstitutifsEnfants as $ecEnfant){
                if($this->isEcMutualiseMaster($ecEnfant, $dto) === true){
                    $elpArray[] = $this->setObjectForSoapCall($ecEnfant, $dto, CodeNatuElpEnum::MATM, $withChecks);
                }
                elseif ($this->isEcMutualise($ecEnfant) === false) {
                    $elpArray[] = $this->setObjectForSoapCall($ecEnfant, $dto, CodeNatuElpEnum::MATI, $withChecks);
                }
            }
        }
        // si c'est une matière standard
        if($hasChildren === false && $this->isEcMutualise($ec) === false){
            $nature = CodeNatuElpEnum::MATI;
            if ($ec->elementConstitutif->getTypeEc()->getType() === TypeUeEcEnum::STAGE){
                $nature = CodeNatuElpEnum::MATS;
            }
            $elpArray[] = $this->setObjectForSoapCall($ec, $dto, $nature, $withChecks);
        }
    }

    /**
     * Ajoute une UE dans le tableau d'ELP
     * @param array &$elpArray Tableau d'ELP dans lequel on insère
     * @param StructureUe $ue La source de données de l'UE
     * @param StructureParcours $dto DTO du parcours
     * @param bool $withChecks Si des messages d'erreurs doivent être générés
     * @return void
     */
    private function addUeToElpArray(array &$elpArray, StructureUe $ue, StructureParcours $dto, bool $withChecks = false) : void {
        if(count($ue->uesEnfants()) > 0){
            // Ne pas insérer les éléments de nature 'CHOI'
            // $elpArray[] = $this->setObjectForSoapCall($ue, $dto, CodeNatuElpEnum::CHOI, $withChecks);
            foreach($ue->uesEnfants() as $ueEnfant){
                $elpArray[] = $this->setObjectForSoapCall($ueEnfant, $dto, CodeNatuElpEnum::UE, $withChecks);
                foreach($ueEnfant->elementConstitutifs as $ecUeEnfant){
                    $this->addEcToElpArray($elpArray, $ecUeEnfant, $dto, $withChecks);
                }
            }
        }else {
            $elpArray[] = $this->setObjectForSoapCall($ue, $dto, CodeNatuElpEnum::UE, $withChecks);
        }
    }

    /**
     * Ajoute un Semestre dans le tableau d'ELP
     * @param array &$elpArray Tableau d'ELP dans lequel on insère
     * @param StructureSemestre $semestre Données sources du semestre
     * @param StructureParcours $dto DTO du parcours
     * @param bool $withChecks Si des messages d'erreurs doivent être générés
     */
    private function addSemestreToElpArray(array &$elpArray, StructureSemestre $semestre, StructureParcours $dto, bool $withChecks = false) : void {
        $elpArray[] = $this->setObjectForSoapCall($semestre, $dto, CodeNatuElpEnum::SEM, $withChecks);
    }

    /**
     * Met en forme les données pour être utilisées par le Web Service Apogee
     * @param ElementPedagogiDTO6 $elp Element à mettre en forme
     * @return stdClass Objet transformé
     */
    private function mapDataForWebService(ElementPedagogiDTO6 $elp) : stdClass {
        $object = new stdClass();
        $object->elementPedagogi = $elp;
        return $object;
    }

    /**
     * Met en forme la liste LSE pour être utilisée par le Web Service Apogee
     * @param ListeElementPedagogiDTO3 $lse Element à mettre en forme
     * @return stdClass Objet transformé
     */
    private function mapDataLseForWebService(ListeElementPedagogiDTO3 $lse) : stdClass {
        $object = new stdClass();
        $object->listeElementPedagogi = $lse;
        return $object;
    }

    /**
     * Récupère les parcours disponibles et valides en base de données
     * @return array Tableau des parcours disponibles à traiter
     */
    private function retrieveParcoursDataFromDatabase(){
        $dataArray = $this->entityManager->getRepository(Formation::class)->findAll();
        $dataArray = array_filter($dataArray, [$this, 'filterFormationByPublicationState']);
        $dataArray = array_map(fn($formation) => $formation->getParcours()->toArray(), $dataArray);
        return array_merge(...$dataArray);
    }

    /**
     * Calcule les charges d'enseignements pour l'element pédagogique fourni
     * @param StructureEc $elementPedagogique Élément pédagogique
     * @return TableauParametrageChargeEnseignementDTO2 Paramètres des charges d'enseignement
     */
    private function configureChargeEnseignementForEC(StructureEc $elementPedagogique) : TableauParametrageChargeEnseignementDTO2|array {
        $typeHeureArray = [];
        if($elementPedagogique->heuresEctsEc->cmPres > 0 || $elementPedagogique->heuresEctsEc->cmDist > 0){
            $nbHeure = $elementPedagogique->heuresEctsEc->cmPres + $elementPedagogique->heuresEctsEc->cmDist;
            $typeHeureArray[] = new TypeHeureDTO(TypeHeureCE::CM, (string)$nbHeure, '300');
        }
        if($elementPedagogique->heuresEctsEc->tdPres > 0 || $elementPedagogique->heuresEctsEc->tdDist > 0){
            $nbHeure = $elementPedagogique->heuresEctsEc->tdPres + $elementPedagogique->heuresEctsEc->tdDist;
            $typeHeureArray[] = new TypeHeureDTO(TypeHeureCE::TD, (string)$nbHeure, '32');
        }
        if($elementPedagogique->heuresEctsEc->tpPres > 0 || $elementPedagogique->heuresEctsEc->tpDist > 0){
            $nbHeure = $elementPedagogique->heuresEctsEc->tpPres + $elementPedagogique->heuresEctsEc->tpDist;
            $typeHeureArray[] = new TypeHeureDTO(TypeHeureCE::TP, (string)$nbHeure, '16');
        }

        if(count($typeHeureArray) > 0){
            $typeHeureArrayDTO = new TableauTypeHeureDTO($typeHeureArray);
            $paramCE = new ParametrageAnnuelCeDTO2('2024', $typeHeureArrayDTO, 'O');
            return [$paramCE];
        }else {
            return [];
        }

    }
    
    /**
     * Crée la liste d'éléments pédagogiques pour les enfants d'un EC à choix
     * @param StructureEc $ec EC à traiter
     * @param string $libelleCourt Libellé court à utiliser
     * @param string $libelleLong Libellé long à utiliser
     * @return ListeElementPedagogiDTO3|null Liste d'éléments pédagogiques s'il y a des enfants, null sinon
     */
    private function getLseObjectForEcChildren(StructureEc $ec, string $libelleCourt, string $libelleLong) : ListeElementPedagogiDTO3|null {
        $return = null;
        if(count($ec->elementsConstitutifsEnfants) > 0){
            $return = new ListeElementPedagogiDTO3($ec->elementConstitutif->getCodeApogee() ?? 'ERROR', 'O', $libelleCourt, $libelleLong, array_map(
                fn($ecEnfant) => $ecEnfant->elementConstitutif->getCodeApogee(),
                $ec->elementsConstitutifsEnfants
            ));
        }
        return $return;
    }

    /**
     * Crée la liste d'éléments pédagogiques pour les enfants d'une UE à choix
     * @param StructureUe $ue UE à traiter
     * @param string $libelleCourt Libellé court à utiliser 
     * @param string $libelleLong Libellé long à utiliser
     * @return ListeElementPedagogiDTO3|null Liste d'éléments pédagogiques s'il y a des enfants, null sinon  
     */
    private function getLseObjectForUeChildren(StructureUe $ue, string $libelleCourt, string $libelleLong) : ListeElementPedagogiDTO3|null {
        $return = null;
        if(count($ue->uesEnfants()) > 0){
            $return = new ListeElementPedagogiDTO3($ue->ue->getCodeApogee() ?? 'ERROR', 'O', $libelleCourt, $libelleLong, array_map(
                fn($ueEnfant) => $ueEnfant->ue->getCodeApogee(),
                $ue->uesEnfants()
            ));
        }
        return $return;
    }

    /**
     * Crée la liste LSE des EC composant une UE
     * @param StructureUe $ue UE à utiliser
     * @param string $libelleCourt Libellé court à utiliser pour la liste
     * @param string $libelleLong Libellé long à utiliser pour la liste
     * @return ListeElementPedagogiDTO3 Liste LSE
     */
    private function getLseEcFromUe(StructureUe $ue, string $libelleCourt, string $libelleLong) : ListeElementPedagogiDTO3 {
        return new ListeElementPedagogiDTO3($ue->ue->getCodeApogee() ?? 'ERROR', 'O', $libelleCourt, $libelleLong, array_map(
            fn($ec) => $ec->elementConstitutif->getCodeApogee(),
            $ue->elementConstitutifs
        ));
    }

    /**
     * Crée la liste LSE des UE composant un Semestre
     * @param StructureSemestre $semestre Semestre à traiter
     * @param string $libCourt Libellé court à utiliser pour la liste
     * @param string $libLong Libellé long à utiliser pour la liste
     * @return ListeElementPedagogiDTO3 Liste LSE
     */
    private function getLseUeFromSemestre(StructureSemestre $semestre, string $libCourt, string $libLong) : ListeElementPedagogiDTO3 {
        return new ListeElementPedagogiDTO3($semestre->semestre->getCodeApogee() ?? 'ERROR', 'O', $libCourt, $libLong, array_map(
            fn($ue) => $ue->ue->getCodeApogee(),
            $semestre->ues()
        ));
    }

    /**
     * Crée le tableau de liste d'éléments pédagogiques pour un semestre. 
     * Descend l'arborescence jusqu'aux EC et leurs enfants possibles
     * @param StructureSemestre $semestre Semestre à utiliser 
     * @param StructureParcours $dto DTO du parcours
     * @return array Tableau de listes LSE
     */
    private function getLseObjectArrayForSemestre(StructureSemestre $semestre, StructureParcours $dto) : array {
        $return = [];
        $libelles = $this->getLibellesForListeUE($semestre, $dto);
        $return[] = $this->getLseUeFromSemestre($semestre, $libelles['libCourt'], $libelles['libLong']);
        foreach($semestre->ues() as $ue){
            if(count($ue->elementConstitutifs) > 0){
                $libelles = $this->getLibellesForListeEC($ue, $dto);
                $return[] = $this->getLseEcFromUe($ue, $libelles['libCourt'], $libelles['libLong']);
            }
            foreach($ue->elementConstitutifs as $ec){
                if(count($ec->elementsConstitutifsEnfants) > 0){
                    $libelles = $this->getLibellesForEcWithChildren($ec, $dto);
                    $return[] = $this->getLseObjectForEcChildren($ec, $libelles['libCourt'], $libelles['libLong']);
                }
            }
            if(count($ue->uesEnfants()) > 0){
                $libelles = $this->getLibellesForUeWithChildren($ue, $dto);
                $return[] = $this->getLseObjectForUeChildren($ue, $libelles['libCourt'], $libelles['libLong']);
                foreach($ue->uesEnfants() as $ueEnfant){
                    if(count($ueEnfant->elementConstitutifs) > 0){
                        $libelles = $this->getLibellesForListeEC($ueEnfant, $dto);
                        $return[] = $this->getLseEcFromUe($ueEnfant, $libelles['libCourt'], $libelles['libLong']);
                    }
                    foreach($ueEnfant->elementConstitutifs as $ec){
                        if(count($ec->elementsConstitutifsEnfants) > 0){
                            $libelles = $this->getLibellesForEcWithChildren($ec, $dto);
                            $return[] = $this->getLseObjectForEcChildren($ec, $libelles['libCourt'], $libelles['libLong']);
                        }
                    }
                }
            }
        }
    
        return $return;
    }

    /**
     * Génère les listes LSE pour un parcours complet
     * @param StructureParcours $parcours Parcours à utiliser
     * @return array Tableau comportant des LSE de type ListeElementPedagogiDTO3
     */
    private function getLseObjectArrayForParcours(StructureParcours $parcours) : array {
        $return = [];
        $libelleListeSemestre = $parcours->parcours->getFormation()->getTypeDiplome()->getLibelleCourt() . " "
            . $parcours->parcours->getFormation()->getSigle() . " "
            . $parcours->parcours->getSigle();
        $libCourtListeSemestre = "SEM " . $libelleListeSemestre;
        $libLongListeSemestre = "Semestres " . $libelleListeSemestre;
        $return[] = [
            new ListeElementPedagogiDTO3('LISTE_SEMESTRE', 'O', $libCourtListeSemestre, $libLongListeSemestre, array_map(
                fn($semestre) => $semestre->semestre->getCodeApogee(),
                $parcours->semestres
            ))
        ];
        foreach($parcours->semestres as $semestre){
            $return[] = $this->getLseObjectArrayForSemestre($semestre, $parcours);
        }
        return array_merge(...$return);
    }

    /**
     * Génère un rapport Excel suivant le tableau de liste (LSE) fourni en paramètre.
     * @param array $lseArray Tableau contenant les listes (LSE)
     * @param string $exportTypeName Texte à faire figurer dans le nom du fichier d'export
     * @return void
     */
    private function generateSpreadsheetForLSE(array $lseArray, string $exportTypeName = ""){
        $headers = ['codListeELP', 'typListeELP', 'libelleCourt', 'libelleLong', 'tableauCodeELP'];
        // arrange data
        $lseArray = array_map(
            fn($lse) => [
                $lse->codListeElp, $lse->typListeElp, $lse->libCourtListeElp, $lse->libListeElp,
                implode(
                    ", ", 
                    array_map(fn($elp) => $elp->codElp,
                        $lse->listElementPedagogi->elementPedagogi                
                    )
                )
            ],
            $lseArray
        );
        // Write to spreadsheet
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->fromArray($headers);
        $activeWorksheet->fromArray($lseArray, startCell: "A2");
        // Write to file
        $now = new DateTime();
        $date = $now->format('d-m-Y_H-i-s');
        $filename = __DIR__ . "/../Service/Apogee/export/{$exportTypeName}-LSE-export-{$date}.xlsx";
        $this->filesystem->dumpFile($filename, "");
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($filename);
    }

    /**
     * Génère les libellés courts et long pour une liste d'UE, raccrochée à un semestre
     * @param StructureSemestre $semestre Semestre contenant les UE
     * @param StructureParcours $dto DTO du parcours concerné
     * @return array Tableau avec les libellés accessibles via les clés 'libCourt' et 'libLong'
     */
    private function getLibellesForListeUE(StructureSemestre $semestre, StructureParcours $dto){
        $typeDiplome = $dto->parcours->getFormation()?->getTypeDiplome()?->getLibelleCourt() ?? "";
        $sigleFormation = $dto->parcours->getFormation()?->getSigle() ?? "";
        $sigleParcours = $dto->parcours->getSigle() ?? "";
        $libelleCourt = "LISTE UE S" . $semestre->semestre->getOrdre() . " " 
        . $sigleFormation . " " . $sigleParcours;
        $libelleLong = "LISTE UE SEMESTRE " . $semestre->semestre->getOrdre() . " " 
            . $typeDiplome . " " . $sigleFormation . " " . $sigleParcours; 
        return [
            'libCourt' => $libelleCourt,
            'libLong' => $libelleLong
        ];
    }

    /**
     * Génère les libellés courts et long pour une liste d'EC
     * @param StructureUe $ue UE contenant la liste d'EC
     * @param StructureParcours $dto DTO du parcours concerné
     * @return array Tableau avec les libellés court et long accessibles via les clés 'libCourt' et 'libLong'
     */
    private function getLibellesForListeEC(StructureUe $ue, StructureParcours $dto){
        $typeDiplome = $dto->parcours->getFormation()?->getTypeDiplome()?->getLibelleCourt() ?? "";
        $sigleFormation = $dto->parcours->getFormation()?->getSigle() ?? "";
        $sigleParcours = $dto->parcours->getSigle() ?? "";
        $libelleCourt = "ECs " . $ue->ue->display() . " S" . $ue->ue->getSemestre()->getOrdre() . " " 
            . $sigleFormation . " " . $sigleParcours;
        $libelleLong = "LISTE EC " . $ue->ue->display() . " S" . $ue->ue->getSemestre()->getOrdre() 
            . " " . $typeDiplome . " " . $sigleFormation . " " . $sigleParcours; 
        return [
            'libCourt' => $libelleCourt,
            'libLong' => $libelleLong
        ];
    }

    /**
     * Genère les libellés pour la liste comportant des EC enfants
     * @param StructureEc $ec EC contenant des enfants
     * @param StructureParcours $dto Parcours concerné
     * @return array Tableau avec les libellés court et long
     */
    private function getLibellesForEcWithChildren(StructureEc $ec, StructureParcours $dto){
        $typeDiplome = $dto->parcours->getFormation()?->getTypeDiplome()?->getLibelleCourt() ?? "";
        $sigleFormation = $dto->parcours->getFormation()?->getSigle() ?? "";
        $sigleParcours = $dto->parcours->getSigle() ?? "";
        $libelleCourt = $ec->elementConstitutif->getCode() . " " . $ec->elementConstitutif->getUe()?->display() 
            . " S" . $ec->elementConstitutif->getUe()?->getSemestre()?->getOrdre() . " "
            . $typeDiplome . " " . $sigleFormation . " " . $sigleParcours;
        $libelleLong = $ec->elementConstitutif->getCode() . " " . $ec->elementConstitutif->getUe()?->display() . " S"
            . $ec->elementConstitutif->getUe()?->getSemestre()?->getOrdre() . " " . $sigleFormation . " ". $sigleParcours;
        return [
            'libCourt' => $libelleCourt,
            'libLong' => $libelleLong
        ];
    }

    /**
     * Génère les libellés pour une liste d'UE avec des enfants
     * @param StructureUe $ue UE avec des enfants
     * @param StructureParcours $dto DTO du parcours concerné
     * @return array Libellés long et court
     */
    private function getLibellesForUeWithChildren(StructureUe $ue, StructureParcours $dto){
        $libelleLong = "LISTE " . $ue->ue->display() . " " . $ue->ue->getSemestre()->display() 
            . " " . $dto->parcours->getFormation()->getSigle() ?? ""; 
        $libelleCourt = $ue->ue->display() . " " . $ue->ue->getSemestre()->display() 
            . " " . $dto->parcours->getFormation()->getSigle() ?? "";
        return [
            'libCourt' => $libelleCourt,
            'libLong' => $libelleLong
        ];
    }
}
