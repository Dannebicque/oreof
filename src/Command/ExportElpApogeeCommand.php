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
use App\Repository\ElementConstitutifRepository;
use App\Service\Apogee\Classes\ElementPedagogiDTO6;
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
    private Filesystem $filesystem;
    private ParameterBagInterface $parameterBag;
    private ?\SoapClient $soapClient;

    public static array $errorMessagesArray = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        ElementConstitutifRepository $elementConstitutifRepository,
        Filesystem $filesystem,
        ParameterBagInterface $parameterBag
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->elementConstitutifRepository = $elementConstitutifRepository;
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
            name: 'parcours-insertion',
            mode: InputOption::VALUE_REQUIRED,
            description: "Insère tous les ELP d'un parcours dans la base de données, via le Web Service"
        )->addOption(
            name: 'parcours-excel-export',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Genère une export de tous les ELP pour un parcours donné.'
        )->addOption(
            name: 'report-missing-values',
            mode: InputOption::VALUE_NONE,
            description: "Compte rendu des parcours qui sont des candidats à l'insertion dans APOTEST et qui sont non conformes"
        )->addOption(
            name: 'check-duplicates',
            mode: InputOption::VALUE_NONE,
            description: "Vérifie s'il y a des doublons sur les codes Apogee depuis la base de données"
        )->addOption(
            name: 'full-verify-data',
            mode: InputOption::VALUE_NONE,
            description: "Instancie tous les ELP des parcours disponibles, et génère un compte-rendu selon les erreurs détectées."
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $mode = $input->getOption('mode');
        $fullExport = $input->getOption('full-excel-export');
        $parcoursExport = $input->getOption('parcours-excel-export');
        $dummyInsertion = $input->getOption('dummy-insertion');
        $parcoursInsertion = $input->getOption('parcours-insertion');
        $checkDuplicates = $input->getOption('check-duplicates');
        $fullVerifyData = $input->getOption('full-verify-data');

        if($mode === "test"){
            // Export total des ELP selon le type : EC, UE ou Semestre
            if($fullExport){
                switch(strtoupper($fullExport)){
                    case "EC":
                        $io->writeln("Génération de l'export Excel...");
                        $this->saveFullExportAsSpreadsheet($output, "EC");
                        break;
                    case "UE":
                        $io->writeln("Génération de l'export Excel...");
                        $this->saveFullExportAsSpreadsheet($output, "UE");
                        break;
                    case "SEMESTRE":
                        $io->writeln("Génération de l'export Excel...");
                        $this->saveFullExportAsSpreadsheet($output, "SEMESTRE");
                        break;
                    default: 
                        $io->warning("Type d'export inconnu. Il devrait être parmi la liste : ['SEMESTRE', 'UE', 'EC']");
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
                $file = __DIR__ . "/../Service/Apogee/export/Parcours-invalides-{$date}";
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
            if($dummyInsertion){
                $io->write("Utilisation du Web Service APOTEST");
                if($this->verifyUserIntent($io, "Voulez-vous vraiment insérer dans APOTEST ?")){
                    // Récupération des donnéees à insérer
                    $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById(405);
                    $dto = $this->getDTOForParcours($parcours);
                    $ec = $dto->semestres[1]->ues()[0]->elementConstitutifs[0];
                    // $elp = new ElementPedagogiDTO6($ec, $dto);
                    $elp = $this->setObjectForSoapCall($ec, $dto, CodeNatuElpEnum::MATI, false);
                    $elp->codElp = 'TEST110';
                    $elp->codNatureElp = 'MATI';
                    $elp->libCourtElp = "TEST WS PHP 10";
                    $elp->libElp = "TEST WEBSERVICE PHP 12022024";
                    dump($elp);
                    if($this->verifyUserIntent($io, "Les données affichées conviennent-elles ?")){
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
                            $io->writeln("Une erreur est survenue durant l'insertion.");
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
            $calculStructure = new CalculStructureParcours($this->entityManager, $this->elementConstitutifRepository);
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
                ), $elp->listParamChargEns instanceof TableauParametrageChargeEnseignementDTO2 ? $elp->listParamChargEns->printInformation() : ""
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
            $elpArray[] = $this->setObjectForSoapCall($ec, $dto, CodeNatuElpEnum::MATM, $withChecks);
        }
        // si l'élément a des enfants, on insère que les enfants
        if($hasChildren){
            foreach($ec->elementsConstitutifsEnfants as $ecEnfant){
                if($this->isEcMutualiseMaster($ecEnfant, $dto) === true){
                    $elpArray[] = $this->setObjectForSoapCall($ecEnfant, $dto, CodeNatuElpEnum::MATM, $withChecks);
                }
                elseif ($this->isEcMutualise($ecEnfant) === false) {
                    $elpArray[] = $this->setObjectForSoapCall($ecEnfant, $dto, CodeNatuElpEnum::CHOI, $withChecks);
                }
            }
        }
        // si c'est une matière standard
        if($hasChildren === false && $this->isEcMutualise($ec) === false){
            $elpArray[] = $this->setObjectForSoapCall($ec, $dto, CodeNatuElpEnum::MATI);
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
            foreach($ue->uesEnfants() as $ueEnfant){
                $elpArray[] = $this->setObjectForSoapCall($ueEnfant, $dto, CodeNatuElpEnum::CHOI, $withChecks);            
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
            $typeHeureArray[] = new TypeHeureDTO(TypeHeureCE::CM, (string)$nbHeure, '55');
        }
        if($elementPedagogique->heuresEctsEc->tdPres > 0 || $elementPedagogique->heuresEctsEc->tdDist > 0){
            $nbHeure = $elementPedagogique->heuresEctsEc->tdPres + $elementPedagogique->heuresEctsEc->tdDist;
            $typeHeureArray[] = new TypeHeureDTO(TypeHeureCE::TD, (string)$nbHeure, '55');
        }
        if($elementPedagogique->heuresEctsEc->tpPres > 0 || $elementPedagogique->heuresEctsEc->tpDist > 0){
            $nbHeure = $elementPedagogique->heuresEctsEc->tpPres + $elementPedagogique->heuresEctsEc->tpDist;
            $typeHeureArray[] = new TypeHeureDTO(TypeHeureCE::TP, (string)$nbHeure, '55');
        }

        if(count($typeHeureArray) > 0){
            $typeHeureArrayDTO = new TableauTypeHeureDTO($typeHeureArray);
            $paramCE = new ParametrageAnnuelCeDTO2('2024', $typeHeureArrayDTO, 'O');
            return [$paramCE];
        }else {
            return [];
        }

    }
    
}
