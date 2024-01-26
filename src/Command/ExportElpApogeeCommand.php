<?php

namespace App\Command;

use App\Classes\CalculStructureParcours;
use App\DTO\StructureEc;
use App\DTO\StructureParcours;
use App\DTO\StructureSemestre;
use App\DTO\StructureUe;
use App\Entity\Parcours;
use App\Repository\ElementConstitutifRepository;
use App\Service\Apogee\Classes\ElementPedagogiDTO6;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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

    public function __construct(
        EntityManagerInterface $entityManager,
        ElementConstitutifRepository $elementConstitutifRepository,
        Filesystem $filesystem
    )
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->elementConstitutifRepository = $elementConstitutifRepository;
        $this->filesystem = $filesystem;
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'mode',
            mode: InputArgument::OPTIONAL,
            description: 'Execution mode : test or production', 
            default: 'test'
        )->addOption(
            'excel-export', 
            'excel',
            InputArgument::OPTIONAL,
            'Génère un export Excel des ELP - Type : Semestre, UE, EC'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $mode = $input->getOption('mode');
        $export = $input->getOption('excel-export');


        if($mode === "test"){
            if($export){
                switch(strtoupper($export)){
                    case "EC":
                        $io->writeln("Génération de l'export Excel...");
                        $this->saveExportAsSpreadsheet($output, "EC");
                        break;
                    case "UE":
                        $io->writeln("Génération de l'export Excel...");
                        $this->saveExportAsSpreadsheet($output, "UE");
                        break;
                    case "SEMESTRE":
                        // $io->writeln("Génération de l'export Excel...");
                        // $this->saveExportAsSpreadsheet($output, "SEMESTRE");
                        break;
                    default: 
                        $io->warning("Type d'export inconnu. Il devrait être parmi la liste : ['SEMESTRE', 'UE', 'EC']");
                        return Command::INVALID;
                }
                $io->success("Fichier généré avec succès.");

                return Command::SUCCESS;
            }       
            
            return Command::SUCCESS;
        }

        elseif($mode === "production"){
            $io->warning("Commande en mode PRODUCTION - O.K");
            return Command::INVALID;
        }
        else{
            $io->error("Given execution mode is invalid. It should be 'test' or 'production'");
            return Command::FAILURE;
        }
    }

    private function setObjectForSoapCall(
        StructureEc|StructureUe|StructureSemestre $elementPedagogique, 
        StructureParcours $dto
    ) : ElementPedagogiDTO6 {
        return new ElementPedagogiDTO6($elementPedagogique, $dto);
    }

    private function saveExportAsSpreadsheet(OutputInterface $output, string $type){
        // retrieve data
        $parcoursArray = $this->entityManager->getRepository(Parcours::class)->findAll();
        $totalElement = count($parcoursArray);
        // progress bar
        $progressBar = new ProgressBar($output, $totalElement);
        // transform into valid soap object
        $soapObjectArray = [];
        foreach($parcoursArray as $parcours){
            $dto = $this->getDTOForParcours($parcours);
            if($type === "EC"){
                foreach($dto->semestres as $semestre){
                    foreach($semestre->ues() as $ue){
                        foreach($ue->elementConstitutifs as $ec){
                            $soapObjectArray[] = $this->setObjectForSoapCall($ec, $dto);
                        }
                    }
                }
                $progressBar->advance();
            }
            elseif ($type === "UE") {
                foreach($dto->semestres as $semestre){
                    foreach($semestre->ues() as $ue){
                        $soapObjectArray[] = $this->setObjectForSoapCall($ue, $dto);
                    }
                }
                $progressBar->advance();
            }
            elseif ($type === "SEMESTRE"){

            }
            
        }
        $this->generateSpreadsheet($soapObjectArray);
    }

    private function getDTOForParcours(Parcours $parcours){
        $calculStructure = new CalculStructureParcours($this->entityManager, $this->elementConstitutifRepository);
        return $calculStructure->calcul($parcours);
    }

    private function generateSpreadsheet(array $ElpArray){
        // spreadsheet headers
        $headers = [
            "codElp", "libCourtElp", "libElp", "codNatureElp",
            "codComposante", "temModaliteControle", "nbrCredits",
            "volume", "uniteVolume", "codPeriode"
        ];
        // cast element in array values
        $ElpArray = array_map(
            fn($elp) => [
                $elp->codElp, $elp->libCourtElp, $elp->libElp, 
                $elp->codNatureElp, $elp->codComposante, $elp->temModaliteControle, 
                $elp->nbrCredits, $elp->volume, $elp->uniteVolume, $elp->codPeriode
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
        $filename = __DIR__ . "/../Service/Apogee/export/ELP-export-{$date}.xlsx";
        $this->filesystem->dumpFile($filename, "");
        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save($filename);
    }

}
