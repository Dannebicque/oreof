<?php

namespace App\Command;

use App\Entity\Formation;
use App\Entity\Parcours;
use App\Service\ParcoursCopyData;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:parcours-copy-data',
    description: 'Commande pour la migration des heures vers les fiches matières',
)]
class ParcoursCopyDataCommand extends Command
{
    private EntityManagerInterface $entityManagerCopyData;

    private EntityManagerInterface $entityManager;

    private ParcoursCopyData $parcoursCopyData;

    private Filesystem $fs;

    public function __construct(
        ParcoursCopyData $parcoursCopyData,
        Filesystem $fs,
        ManagerRegistry $doctrine
    )
    {
        parent::__construct();
        $this->entityManager = $doctrine->getManager('default');
        $this->entityManagerCopyData = $doctrine->getManager('parcours_copy');
        $this->parcoursCopyData = $parcoursCopyData;
        $this->fs = $fs;
    }

    protected function configure(): void
    {
        $this
        ->addOption(
            name: 'dto-pdf-export', 
            mode: InputOption::VALUE_REQUIRED, 
            description: 'Commande pour exporter un DTO de parcours au format PDF'
        )->addOption(
            name: 'compare-two-dto',
            mode: InputOption::VALUE_REQUIRED,
            description: "Compare deux DTO avant et après copie. Donner deux Identifiants en entrée"
        )->addOption(
            name: 'after-copy',
            mode: InputOption::VALUE_NONE,
            description: 'Si le résultat doit être généré après copie sur les fiches matières'
        )->addOption(
            name: 'test-copy-database',
            mode: InputOption::VALUE_NONE,
            description: "Copie les heures sur les fiches matières dans une base de données de test"
        )->addOption(
            name: 'from-copy',
            mode: InputOption::VALUE_NONE,
            description: "Option pour réaliser la commande depuis la base où les données sont copiées"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '2500M');

        $io = new SymfonyStyle($input, $output);

        $dtoPdfExport = $input->getOption('dto-pdf-export');
        $afterCopy = $input->getOption('after-copy');
        $compareTwoDTO = $input->getOption('compare-two-dto');
        $testCopyDatabase = $input->getOption('test-copy-database');
        $fromCopy = $input->getOption('from-copy');

        if($dtoPdfExport){
            try{
                $io->writeln("Récupération du parcours en base de données...\n");
                $fromCopyFilename = "";
                $dtoFromCopy = false;
                if($fromCopy){
                    $parcours = $this->entityManagerCopyData->getRepository(Parcours::class)->findOneById($dtoPdfExport);
                    $fromCopyFilename = "-from-copy";
                    $dtoFromCopy = true;
                }else {
                    $parcours = $this->entityManager->getRepository(Parcours::class)->findOneById($dtoPdfExport);
                }
                if($parcours){
                    $io->writeln("[O.K] - Parcours trouvé : {$parcours->getDisplay()}\n");
                    $io->writeln("Génération de l'export PDF en cours...");

                    $now = (new DateTime())->format('d-m-Y_H-i-s');
                    $path = __DIR__ . "/../../export/";
                    $fileName = "{$now}-Maquette-DTO-Parcours-{$dtoPdfExport}" . $fromCopyFilename;
                    if($afterCopy){
                        $fileName .= "-after-copy.pdf";
                        $pdf = $this->parcoursCopyData->exportDTOAsPdf($parcours, true, true, fromCopy: $dtoFromCopy);
                    }else {
                        $fileName .= "-initial.pdf";
                        $pdf = $this->parcoursCopyData->exportDTOAsPdf($parcours, false, fromCopy: $dtoFromCopy);
                    }

                    $this->fs->appendToFile($path . $fileName, $pdf);
                    $io->writeln("Fichier généré avec succès. (Dossier '/export')");

                    return Command::SUCCESS;
                }
                else {
                    $io->warning("Aucun parcours trouvé pour cet identifiant. ({$dtoPdfExport})");
                    return Command::FAILURE;
                }
                }
                catch(\Exception $e){
                    $io->writeln("Une erreur est survenue.");
                    $io->writeln("Message d'erreur : {$e->getMessage()}");
        
                    return Command::FAILURE;
            }
        }

        else if($compareTwoDTO){
            if(!is_numeric($compareTwoDTO)){
                $io->warning("L'identifiant du parcours n'est pas un nombre ({$compareTwoDTO})");
                return Command::INVALID;
            }
            $io->writeln("Comparaison des deux DTO avant et après copie.");
            $io->writeln("Récupération du parcours...");
            $parcours = $this->entityManagerCopyData->getRepository(Parcours::class)->findOneById($compareTwoDTO);
            if($parcours){
                $io->writeln("[O.K] - Parcours trouvé : {$parcours->getDisplay()}");

                $dtoBefore = $this->parcoursCopyData->getDTOForParcours($parcours);
                if($fromCopy){
                    $dtoAfter = $this->parcoursCopyData->getDTOForParcours($parcours, true, true, true);
                }else {
                    $dtoAfter = $this->parcoursCopyData->getDTOForParcours($parcours, true, true);
                }
                $isEqual = $this->parcoursCopyData->compareTwoDTO($dtoBefore, $dtoAfter);

                if($isEqual){
                    $io->success("Les deux DTO sont identiques, avant et après copie.");
                    return Command::SUCCESS;
                }else {
                    $io->writeln("Les deux DTO sont différents");
                    dump($this->parcoursCopyData::$errorMessageArray);
                    return Command::FAILURE;
                }
            }

            $io->warning("Aucun parcours trouvé pour cet identifiant. ({$compareTwoDTO})");
            return Command::INVALID;
        }

        else if($testCopyDatabase){
            $this->parcoursCopyData->copyDataForAllParcoursInDatabase($io);
            return Command::SUCCESS;
        }

        $io->warning("Option non reconnue. Doit être parmi ['dto-pdf-export']\n");

        return Command::INVALID;
    }
}
